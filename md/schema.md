# Схема БД: сервис смен (MVP, автосервис)

Версия от 02.11.2026 (смена ссылается на назначение вместо отдела и должности). Стек: Symfony + Doctrine + PostgreSQL.

## Содержание

1. [Общие правила](#общие-правила)
2. Люди: [users](#users), [employee](#employee), [client](#client)
3. Оргструктура: [location](#location), [department](#department), [position](#position), [employee_assignment](#employee_assignment)
4. Смены: [shift](#shift)
5. Каталог: [service_type](#service_type), [service_checklist_item](#service_checklist_item), [product](#product)
6. Заказы: [work_object](#work_object), [work_order](#work_order), [work_order_product](#work_order_product)
7. Задачи: [task](#task), [task_checklist_item](#task_checklist_item), [task_status_change](#task_status_change)
8. Деньги: [ledger_entry](#ledger_entry)
9. [Политика удаления](#политика-удаления)
10. [Что отложено после MVP](#что-отложено-после-mvp)

---

## Общие правила

**Именование.** Таблицы в единственном числе (`users` исключение: `user` зарезервировано в PostgreSQL). Внешние ключи с суффиксом `_id`. Моменты времени с суффиксом `_at`.

**Ключи.** `id` — int autoincrement, первичный ключ.

**Деньги.** Только в копейках, `bigint`. Никаких float.

**Даты и время.**
- `date`: значение вводит человек (дата найма, дата закрытия точки, дата архивации). Для таких дат CHECK `<= CURRENT_DATE`, будущие даты запрещены.
- `timestamptz`: момент фиксирует система (открытие смены, закрытие заказа, отметка чек-листа).

**created_at / updated_at.** Есть у всех таблиц, кроме неизменяемых журналов (`ledger_entry`, `task_status_change`, там только `created_at`). Оба `timestamptz not null`, при создании `updated_at = created_at`. Заполняет собственный `TimestampableTrait` (lifecycle callbacks, тип `datetimetz_immutable`). Готовый trait из Gedmo не подходит: он пишет время без часового пояса.

**Статусы и списки значений.** В базе `varchar` + CHECK со списком. В коде PHP enum (backed enum, маппинг через `enumType` в Doctrine). Тип ENUM PostgreSQL не используем.

**Внешние ключи.** По умолчанию `ON DELETE RESTRICT`. `CASCADE` только для «частей» сущности (пункты чек-листов, история задачи).

**Мягкое удаление.** Справочники не удаляются, а архивируются (`archived_at`) или закрываются (`closed_at`). Люди деактивируются (`deactivated_at`), сотрудники увольняются (`fired_at`).

**Снимки.** Всё, что влияет на деньги и может измениться в каталоге, копируется в момент использования: ставка в смену, цены в задачу и в строку товара, пункты чек-листа в задачу, итог в заказ-наряд.

**Что не делает Doctrine сама.** CHECK, индексы по выражению (`lower(name)`) и частичные индексы пишутся руками в миграциях. Следить, чтобы `doctrine:migrations:diff` не пытался их удалить. Индексы на внешние ключи `many-to-one` Doctrine создаёт сама.

**Где живут правила.**
- В базе: всё, что видно в одной строке (CHECK) и уникальность (UNIQUE, частичные индексы).
- В коде: всё, что затрагивает несколько таблиц. Каждую таблицу с деньгами или статусами меняет только свой сервис. Переходы статусов через Symfony Workflow. При переходах блокировка строк (`PESSIMISTIC_WRITE`) против гонок.

**Роли и доступ.** Роли в `users.roles`. Права через Voters. Суперадмин видит всё, админ только свою точку (через свои назначения), сотрудник свои смены и задачи, клиент свои заказ-наряды.

---

## Люди

### users

Учётная запись любого человека: сотрудника, клиента, админа. Один человек = одна запись, даже если он и сотрудник, и клиент.

| Поле | Тип | Обяз. | Описание |
|---|---|---|---|
| id | int | да | ПК |
| email | varchar(255) | да | Уникальный. Хранится в нижнем регистре |
| phone | varchar(30) | да | Уникальный. Нормализуется в коде к одному формату |
| password | varchar(255) | да | Хэш пароля |
| roles | json | да | Список ролей, по умолчанию `'[]'` |
| first_name | varchar(100) | да | Имя |
| last_name | varchar(100) | да | Фамилия |
| patronymic | varchar(100) | нет | Отчество |
| birthday | date | нет | Дата рождения. Проверка разумности в валидаторе, не в SQL |
| gender | varchar(1) | нет | `m` или `f` |
| deactivated_at | timestamptz | нет | Когда аккаунт отключён. Пусто = активен |
| created_at, updated_at | timestamptz | да | |

**Ограничения в БД:**
```sql
UNIQUE (email)
UNIQUE (phone)
CHECK (email = lower(email))
CHECK (gender IN ('m', 'f'))
```

**Правила в коде:**
- Роли (`ROLE_SUPER_ADMIN`, `ROLE_ADMIN`, `ROLE_EMPLOYEE`, `ROLE_CLIENT`) меняются только через `StaffService` / `ClientService` вместе с созданием профиля `employee` / `client`.
- Удалять пользователя нельзя. По требованию о персональных данных — обезличить: стереть ФИО, телефон, почту, дату рождения, поставить `deactivated_at`.

### employee

Профиль сотрудника. Ставки и должности здесь не хранятся, они в назначениях.

| Поле | Тип | Обяз. | Описание |
|---|---|---|---|
| id | int | да | ПК |
| user_id | → users | да | one-to-one, уникальный, RESTRICT |
| balance | bigint | да | Текущий баланс в копейках, по умолчанию 0. **Кэш журнала** |
| hired_at | date | да | Дата найма |
| fired_at | date | нет | Дата увольнения |
| created_at, updated_at | timestamptz | да | |

**Ограничения в БД:**
```sql
UNIQUE (user_id)
CHECK (fired_at IS NULL OR fired_at >= hired_at)
```

**Правила в коде:**
- `balance` меняет только `LedgerService`, в одной транзакции с записью в журнал. Сеттера `setBalance` нет.
- Консольная команда сверки: `balance` = сумма журнала.
- Увольнение закрывает все действующие назначения. Нельзя уволить при открытой смене.

### client

Профиль клиента.

| Поле | Тип | Обяз. | Описание |
|---|---|---|---|
| id | int | да | ПК |
| user_id | → users | да | one-to-one, уникальный, RESTRICT |
| note | text | нет | Заметка админа о клиенте |
| created_at, updated_at | timestamptz | да | |

```sql
UNIQUE (user_id)
```

---

## Оргструктура

### location

Точка (филиал автосервиса).

| Поле | Тип | Обяз. | Описание |
|---|---|---|---|
| id | int | да | ПК |
| name | varchar(255) | да | Уникальное без учёта регистра, навсегда (имя закрытой точки не переиспользуется) |
| phone | varchar(30) | нет | |
| address | varchar(255) | да | Полный адрес |
| closed_at | date | нет | Дата закрытия, вводит админ |
| created_at, updated_at | timestamptz | да | |

```sql
CREATE UNIQUE INDEX location_name_lower_uniq ON location (lower(name));
CHECK (closed_at <= CURRENT_DATE)
```

**Правила в коде:**
- Нельзя закрыть точку, пока есть открытые смены, незавершённые задачи или открытые заказ-наряды.
- Проверить, что после даты закрытия на точке ничего не происходило.
- При закрытии каскадно закрыть отделы и назначения.
- Повторное открытие — через `reopen` (сбросить `closed_at`).

### department

Отдел (участок) точки.

| Поле | Тип | Обяз. | Описание |
|---|---|---|---|
| id | int | да | ПК |
| location_id | → location | да | RESTRICT |
| name | varchar(255) | да | Уникально среди незакрытых отделов точки |
| closed_at | date | нет | Дата закрытия |
| created_at, updated_at | timestamptz | да | |

```sql
CREATE UNIQUE INDEX department_location_name_active
    ON department (location_id, lower(name))
    WHERE closed_at IS NULL;
CHECK (closed_at <= CURRENT_DATE)
```

**В коде:** закрытие отдела закрывает его назначения; нельзя закрыть при открытых сменах.

### position

Справочник должностей (слесарь, электрик, админ).

| Поле | Тип | Обяз. | Описание |
|---|---|---|---|
| id | int | да | ПК |
| name | varchar(255) | да | Уникальное без учёта регистра |
| archived_at | date | нет | Дата архивации |
| created_at, updated_at | timestamptz | да | |

```sql
CREATE UNIQUE INDEX position_name_lower_uniq ON position (lower(name));
CHECK (archived_at <= CURRENT_DATE)
```

**В коде:** нельзя архивировать при действующих назначениях.

### employee_assignment

Назначение: кто, в каком отделе, на какой должности и по какой ставке может работать. Один сотрудник может иметь несколько назначений.

| Поле | Тип | Обяз. | Описание |
|---|---|---|---|
| id | int | да | ПК |
| employee_id | → employee | да | RESTRICT |
| position_id | → position | да | RESTRICT |
| department_id | → department | да | RESTRICT |
| hourly_rate | bigint | да | Ставка в копейках за час, `>= 0` (0 — например, админ) |
| started_at | date | да | Начало действия |
| ended_at | date | нет | Конец действия, ставится день в день |
| created_at, updated_at | timestamptz | да | |

```sql
CHECK (hourly_rate >= 0)
CHECK (ended_at IS NULL OR (ended_at >= started_at AND ended_at <= CURRENT_DATE))
CREATE UNIQUE INDEX employee_assignment_unique
    ON employee_assignment (employee_id, department_id, position_id)
    WHERE ended_at IS NULL;
```

**В коде:** `started_at >= hired_at`; нельзя назначить в закрытый отдел, на архивную должность или уволенному. Будущие даты не используются.

**Неизменяемость.** Сотрудник, отдел и должность задаются только в конструкторе сущности, сеттеров для них нет: на назначение ссылаются смены, и изменение переписало бы прошлое. Менять можно только ставку (в смене она снимок) и дату окончания. Перевод на другую должность или в другой отдел = `AssignmentService::transfer()`: завершить старое назначение и создать новое в одной транзакции.

---

## Смены

### shift

И заявка на смену, и сама смена — одна строка. Графика нет: сотрудник сам подаёт заявку, админ подтверждает и закрывает. У каждого своя смена.

| Поле | Тип | Обяз. | Описание |
|---|---|---|---|
| id | int | да | ПК |
| status | varchar(20) | да | `requested`, `rejected`, `open`, `closed`, `forced_closed` |
| employee_assignment_id | → employee_assignment | да | Назначение, по которому работает смена. Отдел, должность и точка берутся через него. RESTRICT |
| employee_id | → employee | да | Сотрудник. Осознанная денормализация: дублирует сотрудника из назначения ради индекса «одна активная смена на человека». RESTRICT |
| opened_at | timestamptz | нет | Момент открытия |
| closed_at | timestamptz | нет | Момент закрытия |
| hourly_rate | bigint | нет | **Снимок** ставки из назначения при открытии |
| decided_by_id | → users | нет | Кто подтвердил или отклонил. RESTRICT |
| closed_by_id | → users | нет | Кто закрыл. RESTRICT |
| created_at, updated_at | timestamptz | да | |

```sql
CHECK (status IN ('requested', 'rejected', 'open', 'closed', 'forced_closed'))
CHECK (closed_at > opened_at)
CHECK (hourly_rate >= 0)

CREATE UNIQUE INDEX shift_one_active_per_employee
    ON shift (employee_id)
    WHERE status IN ('requested', 'open');

-- поля соответствуют статусу
ALTER TABLE shift ADD CONSTRAINT shift_status_fields_check CHECK (
    (status = 'requested'
        AND decided_by_id IS NULL
        AND opened_at IS NULL AND hourly_rate IS NULL
        AND closed_at IS NULL AND closed_by_id IS NULL)
 OR (status = 'rejected'
        AND decided_by_id IS NOT NULL
        AND opened_at IS NULL AND hourly_rate IS NULL
        AND closed_at IS NULL AND closed_by_id IS NULL)
 OR (status = 'open'
        AND decided_by_id IS NOT NULL
        AND opened_at IS NOT NULL AND hourly_rate IS NOT NULL
        AND closed_at IS NULL AND closed_by_id IS NULL)
 OR (status IN ('closed', 'forced_closed')
        AND decided_by_id IS NOT NULL
        AND opened_at IS NOT NULL AND hourly_rate IS NOT NULL
        AND closed_at IS NOT NULL AND closed_by_id IS NOT NULL)
);
```

**Правила в коде:**
- Переходы через Workflow: `requested → open | rejected`, `open → closed | forced_closed`.
- `employee_id` смены = `employee_id` назначения (проверка при создании заявки).
- Заявку можно подать только по действующему назначению (`ended_at` пусто). При открытии ещё раз проверить, что назначение действует, и скопировать ставку.
- Смены точки: смена → назначение → отдел → точка.
- Одна заявка или открытая смена на человека (гарантирует индекс).
- Закрытие смены с незавершёнными задачами: предупреждение + подтверждение. Незавершённые задачи возвращаются в `new` без смены, оплата за них не начисляется. Почасовая начисляется всегда.
- Почасовая = минуты смены × ставка / 60, запись `shift` в журнал. При сумме 0 запись не создаётся.
- Заявку в статусе `requested` сотрудник может удалить сам.

---

## Каталог

### service_type

Услуга — шаблон задачи. Имя не `service`, чтобы не путать с сервисами Symfony.

| Поле | Тип | Обяз. | Описание |
|---|---|---|---|
| id | int | да | ПК |
| name | varchar(255) | да | Уникальное без учёта регистра |
| description | text | нет | |
| client_price | bigint | нет | Цена для клиента, `> 0`. Пусто у внутренних услуг |
| employee_pay | bigint | да | Оплата исполнителю, `>= 0` |
| duration_minutes | int | да | Норматив времени, `> 0`. Не дедлайн |
| required_position_id | → position | нет | Рекомендуемая должность. RESTRICT |
| is_internal | bool | да | Внутренняя (уборка, приёмка товара), по умолчанию false |
| archived_at | date | нет | |
| created_at, updated_at | timestamptz | да | |

```sql
CREATE UNIQUE INDEX service_type_name_lower_uniq ON service_type (lower(name));
CHECK (client_price > 0)
CHECK (employee_pay >= 0)
CHECK (duration_minutes > 0)
CHECK (archived_at <= CURRENT_DATE)
CHECK (
    (is_internal = true  AND client_price IS NULL)
 OR (is_internal = false AND client_price IS NOT NULL)
)
```

### service_checklist_item

Пункты шаблонного чек-листа услуги. При создании задачи копируются в `task_checklist_item`.

| Поле | Тип | Обяз. | Описание |
|---|---|---|---|
| id | int | да | ПК |
| service_type_id | → service_type | да | CASCADE |
| position | int | да | Порядковый номер |
| text | varchar(255) | да | Текст пункта |
| created_at, updated_at | timestamptz | да | |

```sql
UNIQUE (service_type_id, position)
```

**В коде:** менять и удалять пункты можно в любой момент, у задач свои копии. Перестановка двух пунктов — в два шага через временный отрицательный номер.

### product

Товар (то, что расходуется: масло, фильтр).

| Поле | Тип | Обяз. | Описание |
|---|---|---|---|
| id | int | да | ПК |
| article | varchar(50) | нет | Артикул, уникальный. Нормализация в сеттере: верхний регистр, без пробелов |
| name | varchar(255) | да | Не уникальное |
| description | text | нет | |
| price | bigint | да | Текущая цена за единицу, `>= 0` |
| unit | varchar(10) | да | `pcs`, `l`, `m`, `kg`. Подписи («шт», «л») в PHP enum `Unit::label()` |
| archived_at | date | нет | |
| created_at, updated_at | timestamptz | да | |

```sql
UNIQUE (article)
CHECK (price >= 0)
CHECK (unit IN ('pcs', 'l', 'm', 'kg'))
CHECK (archived_at <= CURRENT_DATE)
```

**В коде:** у товара, который уже есть в заказ-нарядах, нельзя менять единицу измерения. Сменилась фасовка — архивировать старый товар и завести новый.

---

## Заказы

### work_object

Объект работы — то, над чем работают (для автосервиса машина). Общее название, чтобы подходило другим отраслям. Класс `WorkObject` (`Object` зарезервировано в PHP).

| Поле | Тип | Обяз. | Описание |
|---|---|---|---|
| id | int | да | ПК |
| client_id | → client | да | **Текущий** владелец. RESTRICT |
| identifier | varchar(50) | нет | Для машин госномер. Уникален среди неархивных |
| attributes | jsonb | да | Постоянные данные объекта: марка, модель, год, VIN. По умолчанию `'{}'` |
| archived_at | date | нет | |
| created_at, updated_at | timestamptz | да | |

```sql
CREATE UNIQUE INDEX work_object_identifier_active
    ON work_object (identifier)
    WHERE archived_at IS NULL;
CHECK (archived_at <= CURRENT_DATE)
```

**Правила в коде:**
- Нормализация `identifier` в сеттере: верхний регистр, без пробелов, кириллица и латиница приводятся к одному алфавиту (А В Е К М Н О Р С Т У Х).
- Продажа машины: меняем `client_id` у объекта, новую запись не создаём. Владелец на момент визита хранится в заказ-наряде.
- Номер перешёл на другую машину: старую архивировать, номер освобождается.
- Структуру `attributes` проверяет валидатор в PHP.

### work_order

Заказ-наряд — визит клиента. Услуги заказа — это его задачи (`task.work_order_id`), отдельной таблицы нет. Мастера выводятся через задачи → смены → сотрудников, не хранятся.

| Поле | Тип | Обяз. | Описание |
|---|---|---|---|
| id | int | да | ПК |
| client_id | → client | нет | **Снимок** владельца на момент визита. Пусто — анонимная покупка. RESTRICT |
| work_object_id | → work_object | нет | Пусто — покупка без ремонта. RESTRICT |
| location_id | → location | да | Точка. RESTRICT |
| visit_info | jsonb | да | Данные визита: пробег, жалоба клиента. По умолчанию `'{}'` |
| status | varchar(20) | да | `open`, `closed`, `cancelled`, по умолчанию `open` |
| final_price_snapshot | bigint | нет | **Снимок** итога при закрытии, `>= 0` |
| created_by_id | → users | да | Админ, принявший заказ. RESTRICT |
| closed_at | timestamptz | нет | Когда закрыли или отменили |
| closed_by_id | → users | нет | Кто закрыл или отменил. RESTRICT |
| paid_at | timestamptz | нет | Когда оплатили |
| payment_method | varchar(20) | нет | `cash`, `card`, `transfer` |
| created_at, updated_at | timestamptz | да | |

```sql
CHECK (status IN ('open', 'closed', 'cancelled'))
CHECK (final_price_snapshot >= 0)

-- есть машина, значит есть клиент
CHECK (work_object_id IS NULL OR client_id IS NOT NULL)

-- поля соответствуют статусу
CHECK (
    (status = 'open'
        AND closed_at IS NULL AND closed_by_id IS NULL AND final_price_snapshot IS NULL)
 OR (status = 'closed'
        AND closed_at IS NOT NULL AND closed_by_id IS NOT NULL AND final_price_snapshot IS NOT NULL)
 OR (status = 'cancelled'
        AND closed_at IS NOT NULL AND closed_by_id IS NOT NULL AND final_price_snapshot IS NULL)
)
CHECK (closed_at IS NULL OR closed_at >= created_at)

-- оплата
CHECK (payment_method IN ('cash', 'card', 'transfer'))
CHECK ((paid_at IS NULL) = (payment_method IS NULL))
CHECK (paid_at IS NULL OR (status = 'closed' AND paid_at >= closed_at))

-- горячий список: открытые заказы точки
CREATE INDEX work_order_open_by_location
    ON work_order (location_id)
    WHERE status = 'open';
```

**Правила в коде:**
- При создании `client_id` = текущий владелец объекта.
- Пока заказ открыт, сумма считается запросом: цены принятых задач + строки товаров (количество × цена). Отменённые задачи не считаются. При закрытии итог записывается в `final_price_snapshot` и больше не меняется.
- Нельзя закрыть, пока есть незавершённые задачи.
- Точка задач = точка заказа.
- История статусов не нужна: три статуса, переходы только вперёд.
- Должники = закрытые заказы с пустым `paid_at`.

### work_order_product

Строки товаров заказ-наряда.

| Поле | Тип | Обяз. | Описание |
|---|---|---|---|
| id | int | да | ПК |
| work_order_id | → work_order | да | RESTRICT |
| product_id | → product | да | RESTRICT |
| quantity | numeric(10,3) | да | Количество, `> 0`, до трёх знаков (4,5 л) |
| unit_price | bigint | да | **Снимок** цены за единицу, `>= 0` |
| created_at, updated_at | timestamptz | да | |

```sql
UNIQUE (work_order_id, product_id)
CHECK (quantity > 0)
CHECK (unit_price >= 0)
```

**Правила в коде:**
- Повторное добавление того же товара увеличивает `quantity` существующей строки, цена остаётся первой.
- Сумма строки = `quantity × unit_price`, не хранится. Округление до копеек в коде.
- Менять и удалять строки можно только у открытого заказа.

---

## Задачи

### task

Задача — единица работы. Может быть частью заказ-наряда или внутренней (без заказа). Исполнитель не хранится: он берётся из смены. Назначить задачу можно только сотруднику на открытой смене.

| Поле | Тип | Обяз. | Описание |
|---|---|---|---|
| id | int | да | ПК |
| name | varchar(255) | да | Название, по умолчанию из услуги |
| description | text | нет | Описание от админа |
| location_id | → location | да | Точка. RESTRICT |
| work_order_id | → work_order | нет | RESTRICT |
| shift_id | → shift | нет | Смена исполнителя. Пусто в `new`. RESTRICT |
| service_type_id | → service_type | нет | Пусто — разовая работа не из каталога. RESTRICT |
| required_position_id | → position | нет | Рекомендуемая должность, по умолчанию из услуги. RESTRICT |
| employee_pay_snapshot | bigint | да | **Снимок** оплаты исполнителю, `>= 0` |
| client_price_snapshot | bigint | нет | **Снимок** цены для клиента, `>= 0`. Пусто у внутренних |
| status | varchar(20) | да | `new`, `assigned`, `in_progress`, `submitted`, `accepted`, `cancelled`, по умолчанию `new` |
| deadline_at | timestamptz | нет | Дедлайн |
| created_by_id | → users | да | RESTRICT |
| created_at, updated_at | timestamptz | да | |

```sql
CHECK (status IN ('new', 'assigned', 'in_progress', 'submitted', 'accepted', 'cancelled'))
CHECK (employee_pay_snapshot >= 0)
CHECK (client_price_snapshot >= 0)

-- смена соответствует статусу
ALTER TABLE task ADD CONSTRAINT task_status_shift_check CHECK (
    (status = 'new' AND shift_id IS NULL)
 OR (status IN ('assigned', 'in_progress', 'submitted', 'accepted') AND shift_id IS NOT NULL)
 OR status = 'cancelled'
);

-- горячий список: активные задачи
CREATE INDEX task_active_by_location
    ON task (location_id, status)
    WHERE status IN ('new', 'assigned', 'in_progress', 'submitted');
```

**Переходы (Workflow):**

| Переход | Из | В | Примечание |
|---|---|---|---|
| assign | new | assigned | Записывается смена. Должность не совпала — предупреждение + подтверждение |
| start | assigned | in_progress | Только в открытой смене |
| submit | in_progress | submitted | |
| accept | submitted | accepted | Начисление `task` в журнал (если оплата > 0) |
| return | submitted | in_progress | Без лимита, комментарий обязателен |
| release | assigned, in_progress, submitted | new | При закрытии смены; смена очищается |
| cancel | new, assigned, in_progress, submitted | cancelled | |
| reopen | cancelled | new | Смена очищается |

**Правила в коде:**
- Снимки цен и пунктов чек-листа берутся из услуги при создании.
- Точка совпадает с точкой смены и заказ-наряда.
- Удалить можно только задачу в `new`, которая ни разу не была на смене.

### task_checklist_item

Пункты чек-листа конкретной задачи (копия шаблона + отметки).

| Поле | Тип | Обяз. | Описание |
|---|---|---|---|
| id | int | да | ПК |
| task_id | → task | да | CASCADE |
| position | int | да | Порядковый номер |
| text | varchar(255) | да | Текст, копия из шаблона или введён вручную |
| checked_at | timestamptz | нет | Когда отметили |
| checked_by_id | → users | нет | Кто отметил. RESTRICT |
| created_at, updated_at | timestamptz | да | |

```sql
UNIQUE (task_id, position)
CHECK ((checked_at IS NULL) = (checked_by_id IS NULL))
```

**Правила в коде:**
- Отмечать можно только в статусе `in_progress`.
- При возврате задачи в `new` отметки не сбрасываются.
- У задачи без услуги админ добавляет пункты вручную.

### task_status_change

История переходов задачи. Неизменяемая. Пишется автоматически подписчиком на события Workflow.

| Поле | Тип | Обяз. | Описание |
|---|---|---|---|
| id | int | да | ПК |
| task_id | → task | да | CASCADE |
| from_status | varchar(20) | нет | Пусто у первой записи (создание) |
| to_status | varchar(20) | да | |
| shift_id | → shift | нет | Смена на момент перехода. RESTRICT |
| comment | text | нет | Обязателен при возврате на доработку |
| changed_by_id | → users | нет | Кто. Пусто = система. RESTRICT |
| created_at | timestamptz | да | Когда |

```sql
CHECK (
    (from_status IS NULL OR from_status IN ('new', 'assigned', 'in_progress', 'submitted', 'accepted', 'cancelled'))
    AND to_status IN ('new', 'assigned', 'in_progress', 'submitted', 'accepted', 'cancelled')
    AND (from_status IS NULL OR from_status <> to_status)
)
CHECK (
    NOT (from_status = 'submitted' AND to_status = 'in_progress')
    OR (comment IS NOT NULL AND btrim(comment) <> '')
)
CREATE INDEX task_status_change_history ON task_status_change (task_id, created_at);
```

Даёт: кто и когда брал задачу, сколько было возвратов и почему, реальное время выполнения против норматива услуги.

---

## Деньги

### ledger_entry

Журнал начислений. Неизменяемый: ошибки правятся корректировками, а не редактированием.

| Поле | Тип | Обяз. | Описание |
|---|---|---|---|
| id | int | да | ПК |
| employee_id | → employee | да | RESTRICT |
| type | varchar(20) | да | `shift`, `task`, `bonus`, `penalty`, `payout`, `correction_plus`, `correction_minus` |
| amount | bigint | да | Сумма в копейках, всегда `> 0`. Знак определяет тип (PHP enum `sign()`) |
| shift_id | → shift | нет | one-to-one, только для `shift`. RESTRICT |
| task_id | → task | нет | one-to-one, только для `task`. RESTRICT |
| corrected_entry_id | → ledger_entry | нет | Исправляемая запись, только для `correction_*`. RESTRICT |
| comment | text | нет | Обязателен для всех типов, кроме `shift` и `task` |
| created_by_id | → users | нет | Кто создал. Пусто = система. RESTRICT |
| created_at | timestamptz | да | |

```sql
UNIQUE (shift_id)
UNIQUE (task_id)
CHECK (type IN ('shift', 'task', 'bonus', 'penalty', 'payout', 'correction_plus', 'correction_minus'))
CHECK (amount > 0)

ALTER TABLE ledger_entry ADD CONSTRAINT ledger_entry_source_check CHECK (
    (type = 'shift' AND shift_id IS NOT NULL AND task_id IS NULL)
 OR (type = 'task'  AND task_id  IS NOT NULL AND shift_id IS NULL)
 OR (type IN ('bonus', 'penalty', 'payout', 'correction_plus', 'correction_minus')
     AND shift_id IS NULL AND task_id IS NULL)
);

ALTER TABLE ledger_entry ADD CONSTRAINT ledger_entry_correction_check CHECK (
    corrected_entry_id IS NULL
 OR (type IN ('correction_plus', 'correction_minus') AND corrected_entry_id <> id)
);

ALTER TABLE ledger_entry ADD CONSTRAINT ledger_entry_comment_check CHECK (
    type IN ('shift', 'task') OR (comment IS NOT NULL AND btrim(comment) <> '')
);

CREATE INDEX ledger_entry_employee_history ON ledger_entry (employee_id, created_at);
```

**Правила в коде (`LedgerService`):**
- Единственный, кто пишет в журнал и меняет `employee.balance`, в одной транзакции с блокировкой сотрудника (`PESSIMISTIC_WRITE`).
- Корректировка: тот же сотрудник, что у исходной записи; сумма корректировок не больше исходной; направление осмысленное.
- Отмена штрафа = `correction_plus` со ссылкой на штраф (`LedgerService::cancel`).
- При сумме 0 запись не создаётся.

---

## Политика удаления

Удаляем только то, на что нет ссылок и что не связано с деньгами. Остальное закрываем, архивируем или отменяем.

| Что | Можно удалить | Иначе |
|---|---|---|
| ledger_entry, закрытые смены, принятые задачи, заказ-наряды | никогда | корректировка / отмена |
| service_type, product, position, location, department, work_object | если нигде не используется (RESTRICT решит) | архивировать / закрыть |
| service_checklist_item | всегда | |
| work_order_product | пока заказ открыт | |
| task_checklist_item | пока задача не принята | |
| task | в `new`, ни разу не была на смене | отменить |
| shift | в `requested` | отклонить |
| employee_assignment | если по нему не было смен | завершить |
| users | никогда | обезличить + `deactivated_at` |

Автоматической чистки по времени в MVP нет.

---

## Что отложено после MVP

- Склад и остатки товаров, закупочная цена (маржа).
- Скидки на заказ и позиции.
- Онлайн-запись клиента на время.
- Несколько исполнителей на одной задаче.
- Гарантия, фото до и после.
- Индекс по `work_order.closed_at` для годовых отчётов.
- GIN-индекс по `jsonb`, когда понадобится поиск по марке и модели.
- Чаты и пуш-уведомления.
