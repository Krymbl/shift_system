# Ручной SQL в миграциях

Здесь собрано всё, что Doctrine не генерирует сама и что дописано в миграции руками:

- индексы по выражению (`lower(name)`) и частичные индексы (`WHERE ...`);
- `COMMENT ON INDEX` — комментарии к индексам, в том числе к сгенерированным `IDX_`/`UNIQ_`;
- `CHECK`-ограничения и `COMMENT ON CONSTRAINT` к ним.

Имена индексов, созданных здесь по выражению или частично, перечислены в
`App\Common\Doctrine\Schema\IgnoredIndexesSchemaManager` — иначе `doctrine:migrations:diff`
будет предлагать их удалить. В атрибутах сущностей такие индексы не объявляются.

`DROP TABLE` уносит с собой индексы, CHECK и комментарии, поэтому в `down()` миграций,
создающих таблицу, ничего из этого отдельно дропать не нужно.

---

## users

```php
$this->addSql("COMMENT ON INDEX UNIQ_1483A5E9E7927C74 IS 'Один email = одна учётная запись'");
$this->addSql("COMMENT ON INDEX UNIQ_1483A5E9444F97DD IS 'Один телефон = одна учётная запись'");

$this->addSql('ALTER TABLE users ADD CONSTRAINT users_email_lower_check CHECK (email = lower(email))');
$this->addSql("COMMENT ON CONSTRAINT users_email_lower_check ON users IS 'Email хранится только в нижнем регистре'");

$this->addSql("ALTER TABLE users ADD CONSTRAINT users_gender_check CHECK (gender IN ('m', 'f'))");
$this->addSql("COMMENT ON CONSTRAINT users_gender_check ON users IS 'Пол может быть только m или f, дублирует PHP enum Gender'");
```

## client

```php
$this->addSql("COMMENT ON INDEX UNIQ_C7440455A76ED395 IS 'Один пользователь = один профиль клиента'");
```

## employee

```php
$this->addSql("COMMENT ON INDEX UNIQ_5D9F75A1A76ED395 IS 'Один пользователь = один профиль сотрудника'");

$this->addSql('ALTER TABLE employee ADD CONSTRAINT employee_fired_at_check CHECK (fired_at IS NULL OR (fired_at >= hired_at AND fired_at <= CURRENT_DATE))');
$this->addSql("COMMENT ON CONSTRAINT employee_fired_at_check ON employee IS 'Дата увольнения не раньше даты найма и не в будущем'");
```

## location

```php
$this->addSql('CREATE UNIQUE INDEX location_name_lower_uniq ON location (lower(name))');
$this->addSql("COMMENT ON INDEX location_name_lower_uniq IS 'Имя точки уникально без учёта регистра и не переиспользуется после закрытия'");

$this->addSql('ALTER TABLE location ADD CONSTRAINT location_closed_at_check CHECK (closed_at <= CURRENT_DATE)');
$this->addSql("COMMENT ON CONSTRAINT location_closed_at_check ON location IS 'Дата закрытия не в будущем'");
```

## department

```php
$this->addSql('CREATE UNIQUE INDEX department_location_name_active_uniq ON department (location_id, lower(name)) WHERE closed_at IS NULL');
$this->addSql("COMMENT ON INDEX department_location_name_active_uniq IS 'Имя отдела уникально в точке среди незакрытых; закрытый отдел освобождает имя'");
$this->addSql("COMMENT ON INDEX IDX_CD1DE18A64D218E IS 'Отделы точки. Нужен для списка отделов и для проверки RESTRICT при удалении точки'");

$this->addSql('ALTER TABLE department ADD CONSTRAINT department_closed_at_check CHECK (closed_at <= CURRENT_DATE)');
$this->addSql("COMMENT ON CONSTRAINT department_closed_at_check ON department IS 'Дата закрытия не в будущем'");
```

## position

```php
$this->addSql('CREATE UNIQUE INDEX position_name_lower_uniq ON position (lower(name))');
$this->addSql("COMMENT ON INDEX position_name_lower_uniq IS 'Название должности уникально без учёта регистра, архивация имя не освобождает'");

$this->addSql('ALTER TABLE position ADD CONSTRAINT position_archived_at_check CHECK (archived_at <= CURRENT_DATE)');
$this->addSql("COMMENT ON CONSTRAINT position_archived_at_check ON position IS 'Дата архивации не в будущем'");
```

## employee_assignment

```php
$this->addSql('CREATE UNIQUE INDEX employee_assignment_active_uniq ON employee_assignment (employee_id, department_id, position_id) WHERE ended_at IS NULL');
$this->addSql("COMMENT ON INDEX employee_assignment_active_uniq IS 'Одно действующее назначение на сотрудника в отделе на должности. Завершённые не мешают назначить заново'");
$this->addSql("COMMENT ON INDEX IDX_A812F46E8C03F15C IS 'Назначения сотрудника: его должности и ставки'");
$this->addSql("COMMENT ON INDEX IDX_A812F46EDD842E46 IS 'Назначения на должность. Нужен, чтобы не архивировать должность с действующими назначениями'");
$this->addSql("COMMENT ON INDEX IDX_A812F46EAE80F5DF IS 'Назначения отдела. Нужен при закрытии отдела и для списка его сотрудников'");

$this->addSql('ALTER TABLE employee_assignment ADD CONSTRAINT employee_assignment_hourly_rate_check CHECK (hourly_rate >= 0)');
$this->addSql("COMMENT ON CONSTRAINT employee_assignment_hourly_rate_check ON employee_assignment IS 'Ставка неотрицательная. Ноль допустим, например для админа'");

$this->addSql('ALTER TABLE employee_assignment ADD CONSTRAINT employee_assignment_started_at_check CHECK (started_at <= CURRENT_DATE)');
$this->addSql("COMMENT ON CONSTRAINT employee_assignment_started_at_check ON employee_assignment IS 'Назначение не начинается в будущем'");

$this->addSql('ALTER TABLE employee_assignment ADD CONSTRAINT employee_assignment_ended_at_check CHECK (ended_at IS NULL OR (ended_at >= started_at AND ended_at <= CURRENT_DATE))');
$this->addSql("COMMENT ON CONSTRAINT employee_assignment_ended_at_check ON employee_assignment IS 'Дата окончания не раньше начала и не в будущем. Пусто = назначение действует'");
```

## shift

```php
$this->addSql("CREATE UNIQUE INDEX shift_employee_active_uniq ON shift (employee_id) WHERE status IN ('requested', 'open')");
$this->addSql("COMMENT ON INDEX shift_employee_active_uniq IS 'Одна заявка или открытая смена на сотрудника. Закрытые и отклонённые не мешают подать новую'");
$this->addSql("COMMENT ON INDEX IDX_A50B3B45A7617643 IS 'Смены по назначению. Через него выбираются смены отдела, должности и точки'");
$this->addSql("COMMENT ON INDEX IDX_A50B3B458C03F15C IS 'Смены сотрудника: его история и текущая смена'");
$this->addSql("COMMENT ON INDEX IDX_A50B3B45E26B496B IS 'Кто подтверждал заявки. Нужен для проверки RESTRICT'");
$this->addSql("COMMENT ON INDEX IDX_A50B3B45E1FA7797 IS 'Кто закрывал смены. Нужен для проверки RESTRICT'");

$this->addSql("ALTER TABLE shift ADD CONSTRAINT shift_status_check CHECK (status IN ('requested', 'rejected', 'open', 'closed', 'forced_closed'))");
$this->addSql("COMMENT ON CONSTRAINT shift_status_check ON shift IS 'Список статусов смены, дублирует PHP enum ShiftStatus'");

$this->addSql('ALTER TABLE shift ADD CONSTRAINT shift_hourly_rate_check CHECK (hourly_rate >= 0)');
$this->addSql("COMMENT ON CONSTRAINT shift_hourly_rate_check ON shift IS 'Снимок ставки неотрицательный. Ноль допустим, например для админа'");

$this->addSql('ALTER TABLE shift ADD CONSTRAINT shift_closed_at_check CHECK (closed_at IS NULL OR closed_at >= opened_at)');
$this->addSql("COMMENT ON CONSTRAINT shift_closed_at_check ON shift IS 'Закрытие не раньше открытия. Нестрого: точность колонок — секунда'");

$this->addSql("ALTER TABLE shift ADD CONSTRAINT shift_status_fields_check CHECK (
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
)");
$this->addSql("COMMENT ON CONSTRAINT shift_status_fields_check ON shift IS 'Набор заполненных полей соответствует статусу: заявка без ставки и дат, открытая без закрытия, закрытая со всеми полями'");
```

## service_type

```php
$this->addSql('CREATE UNIQUE INDEX service_type_name_lower_uniq ON service_type (lower(name))');
$this->addSql("COMMENT ON INDEX service_type_name_lower_uniq IS 'Название услуги уникально без учёта регистра, архивация имя не освобождает'");
$this->addSql("COMMENT ON INDEX IDX_429DE3C59DC742D6 IS 'Услуги по рекомендуемой должности. Нужен, чтобы не архивировать используемую должность'");

$this->addSql('ALTER TABLE service_type ADD CONSTRAINT service_type_client_price_check CHECK (client_price > 0)');
$this->addSql("COMMENT ON CONSTRAINT service_type_client_price_check ON service_type IS 'Цена для клиента строго больше нуля. Пусто у внутренних услуг'");

$this->addSql('ALTER TABLE service_type ADD CONSTRAINT service_type_employee_pay_check CHECK (employee_pay >= 0)');
$this->addSql("COMMENT ON CONSTRAINT service_type_employee_pay_check ON service_type IS 'Оплата исполнителю неотрицательная. Ноль допустим у внутренних услуг'");

$this->addSql('ALTER TABLE service_type ADD CONSTRAINT service_type_duration_minutes_check CHECK (duration_minutes > 0)');
$this->addSql("COMMENT ON CONSTRAINT service_type_duration_minutes_check ON service_type IS 'Норматив времени строго больше нуля. Это норматив, а не дедлайн задачи'");

$this->addSql('ALTER TABLE service_type ADD CONSTRAINT service_type_archived_at_check CHECK (archived_at <= CURRENT_DATE)');
$this->addSql("COMMENT ON CONSTRAINT service_type_archived_at_check ON service_type IS 'Дата архивации не в будущем'");

$this->addSql("ALTER TABLE service_type ADD CONSTRAINT service_type_internal_price_check CHECK (
    (is_internal = true  AND client_price IS NULL)
 OR (is_internal = false AND client_price IS NOT NULL)
)");
$this->addSql("COMMENT ON CONSTRAINT service_type_internal_price_check ON service_type IS 'Внутренняя услуга без цены для клиента, обычная — с ценой. Поля ставятся парой через фабрики internal() и billable()'");
```

## service_checklist_item

```php
$this->addSql("COMMENT ON INDEX service_checklist_item_by_service_type_position IS 'Чек-лист услуги в порядке позиций. Уникальности нет: сервис перенумеровывает пункты подряд при сохранении списка'");
$this->addSql("COMMENT ON INDEX IDX_9A8D7D4FAC8DE0F IS 'Пункты чек-листа услуги. Нужен для CASCADE при удалении услуги'");

$this->addSql('ALTER TABLE service_checklist_item ADD CONSTRAINT service_checklist_item_position_check CHECK (position > 0)');
$this->addSql("COMMENT ON CONSTRAINT service_checklist_item_position_check ON service_checklist_item IS 'Позиция начинается с единицы'");
```

## product

```php
$this->addSql("COMMENT ON INDEX UNIQ_D34A04AD23A0E66 IS 'Артикул уникален среди всех товаров, включая архивные'");

$this->addSql('ALTER TABLE product ADD CONSTRAINT product_price_check CHECK (price >= 0)');
$this->addSql("COMMENT ON CONSTRAINT product_price_check ON product IS 'Цена неотрицательная. Ноль допустим, например расходник в подарок'");

$this->addSql("ALTER TABLE product ADD CONSTRAINT product_unit_check CHECK (unit IN ('pcs', 'l', 'm', 'kg'))");
$this->addSql("COMMENT ON CONSTRAINT product_unit_check ON product IS 'Список единиц измерения, дублирует PHP enum ProductUnit'");

$this->addSql('ALTER TABLE product ADD CONSTRAINT product_archived_at_check CHECK (archived_at <= CURRENT_DATE)');
$this->addSql("COMMENT ON CONSTRAINT product_archived_at_check ON product IS 'Дата архивации не в будущем'");
```

## work_object

```php
$this->addSql('CREATE UNIQUE INDEX work_object_identifier_active_uniq ON work_object (identifier) WHERE archived_at IS NULL');
$this->addSql("COMMENT ON INDEX work_object_identifier_active_uniq IS 'Идентификатор уникален только среди неархивных объектов: архивный освобождает его для нового'");
$this->addSql("COMMENT ON INDEX IDX_3546DCE19EB6921 IS 'Объекты клиента. Нужен для списка его объектов и для проверки RESTRICT при удалении клиента'");

$this->addSql('ALTER TABLE work_object ADD CONSTRAINT work_object_archived_at_check CHECK (archived_at <= CURRENT_DATE)');
$this->addSql("COMMENT ON CONSTRAINT work_object_archived_at_check ON work_object IS 'Дата архивации не в будущем'");
```

## work_order

```php
$this->addSql("CREATE INDEX work_order_open_by_location ON work_order (location_id) WHERE status = 'open'");
$this->addSql("COMMENT ON INDEX work_order_open_by_location IS 'Горячий список открытых заказов точки. Закрытые и отменённые в индекс не попадают'");
$this->addSql("COMMENT ON INDEX IDX_DDD2E8B764D218E IS 'Все заказы точки, включая закрытые. Нужен для проверки RESTRICT при удалении точки'");
$this->addSql("COMMENT ON INDEX IDX_DDD2E8B719EB6921 IS 'Заказы клиента: его история визитов и долги'");
$this->addSql("COMMENT ON INDEX IDX_DDD2E8B74125FE7E IS 'Заказы по объекту работы: история обслуживания'");
$this->addSql("COMMENT ON INDEX IDX_DDD2E8B7B03A8386 IS 'Заказы, принятые пользователем. Нужен для проверки RESTRICT'");
$this->addSql("COMMENT ON INDEX IDX_DDD2E8B7E1FA7797 IS 'Заказы, закрытые пользователем. Нужен для проверки RESTRICT'");

$this->addSql("ALTER TABLE work_order ADD CONSTRAINT work_order_status_check CHECK (status IN ('open', 'closed', 'cancelled'))");
$this->addSql("COMMENT ON CONSTRAINT work_order_status_check ON work_order IS 'Список статусов заказа, дублирует PHP enum WorkOrderStatus'");

$this->addSql('ALTER TABLE work_order ADD CONSTRAINT work_order_final_price_check CHECK (final_price_snapshot >= 0)');
$this->addSql("COMMENT ON CONSTRAINT work_order_final_price_check ON work_order IS 'Итог заказа неотрицательный'");

$this->addSql('ALTER TABLE work_order ADD CONSTRAINT work_order_client_object_check CHECK (work_object_id IS NULL OR client_id IS NOT NULL)');
$this->addSql("COMMENT ON CONSTRAINT work_order_client_object_check ON work_order IS 'Есть объект работы — значит есть и клиент: анонимным бывает только заказ без объекта'");

$this->addSql("ALTER TABLE work_order ADD CONSTRAINT work_order_status_fields_check CHECK (
    (status = 'open'
        AND closed_at IS NULL AND closed_by_id IS NULL AND final_price_snapshot IS NULL)
 OR (status = 'closed'
        AND closed_at IS NOT NULL AND closed_by_id IS NOT NULL AND final_price_snapshot IS NOT NULL)
 OR (status = 'cancelled'
        AND closed_at IS NOT NULL AND closed_by_id IS NOT NULL AND final_price_snapshot IS NULL)
)");
$this->addSql("COMMENT ON CONSTRAINT work_order_status_fields_check ON work_order IS 'Набор заполненных полей соответствует статусу: у открытого пусто, у закрытого есть итог, у отменённого итога нет'");

$this->addSql('ALTER TABLE work_order ADD CONSTRAINT work_order_closed_at_check CHECK (closed_at IS NULL OR closed_at >= created_at)');
$this->addSql("COMMENT ON CONSTRAINT work_order_closed_at_check ON work_order IS 'Закрытие не раньше создания заказа'");

$this->addSql("ALTER TABLE work_order ADD CONSTRAINT work_order_payment_method_check CHECK (payment_method IN ('cash', 'card', 'transfer'))");
$this->addSql("COMMENT ON CONSTRAINT work_order_payment_method_check ON work_order IS 'Список способов оплаты, дублирует PHP enum PaymentMethod'");

$this->addSql('ALTER TABLE work_order ADD CONSTRAINT work_order_payment_pair_check CHECK ((paid_at IS NULL) = (payment_method IS NULL))');
$this->addSql("COMMENT ON CONSTRAINT work_order_payment_pair_check ON work_order IS 'Момент оплаты и способ заполняются только вместе'");

$this->addSql("ALTER TABLE work_order ADD CONSTRAINT work_order_paid_at_check CHECK (paid_at IS NULL OR (status = 'closed' AND paid_at >= closed_at))");
$this->addSql("COMMENT ON CONSTRAINT work_order_paid_at_check ON work_order IS 'Оплатить можно только закрытый заказ, и не раньше его закрытия'");
```

## work_order_product

```php
$this->addSql("COMMENT ON INDEX work_order_product_uniq IS 'Один товар встречается в заказе один раз: повторное добавление увеличивает количество существующей строки'");
$this->addSql("COMMENT ON INDEX IDX_65EDD4D2582AE764 IS 'Строки заказ-наряда. По ним считается его итог'");
$this->addSql("COMMENT ON INDEX IDX_65EDD4D24584665A IS 'Строки по товару. Нужен, чтобы не архивировать используемый товар и не менять ему единицу измерения'");

$this->addSql('ALTER TABLE work_order_product ADD CONSTRAINT work_order_product_quantity_check CHECK (quantity > 0)');
$this->addSql("COMMENT ON CONSTRAINT work_order_product_quantity_check ON work_order_product IS 'Количество строго больше нуля: строка с нулём не нужна, её удаляют'");

$this->addSql('ALTER TABLE work_order_product ADD CONSTRAINT work_order_product_unit_price_check CHECK (unit_price >= 0)');
$this->addSql("COMMENT ON CONSTRAINT work_order_product_unit_price_check ON work_order_product IS 'Цена за единицу неотрицательная. Ноль допустим, например подарок'");
```

## task

```php
$this->addSql("CREATE INDEX task_active_by_location ON task (location_id, status) WHERE status IN ('new', 'assigned', 'in_progress', 'submitted')");
$this->addSql("COMMENT ON INDEX task_active_by_location IS 'Горячий список активных задач точки. Принятые и отменённые в индекс не попадают'");
$this->addSql("COMMENT ON INDEX IDX_527EDB2564D218E IS 'Все задачи точки, включая принятые. Нужен для проверки RESTRICT при удалении точки'");
$this->addSql("COMMENT ON INDEX IDX_527EDB25582AE764 IS 'Задачи заказ-наряда. По ним считается его итог'");
$this->addSql("COMMENT ON INDEX IDX_527EDB25BB70BC0E IS 'Задачи смены. По ним видно, что делал сотрудник и что вернуть в пул при закрытии'");
$this->addSql("COMMENT ON INDEX IDX_527EDB25AC8DE0F IS 'Задачи по услуге. Нужен, чтобы не архивировать используемую услугу'");
$this->addSql("COMMENT ON INDEX IDX_527EDB259DC742D6 IS 'Задачи по требуемой должности. Нужен, чтобы не архивировать используемую должность'");
$this->addSql("COMMENT ON INDEX IDX_527EDB25B03A8386 IS 'Задачи, созданные пользователем. Нужен для проверки RESTRICT'");

$this->addSql("ALTER TABLE task ADD CONSTRAINT task_status_check CHECK (status IN ('new', 'assigned', 'in_progress', 'submitted', 'accepted', 'cancelled'))");
$this->addSql("COMMENT ON CONSTRAINT task_status_check ON task IS 'Список статусов задачи, дублирует PHP enum TaskStatus'");

$this->addSql('ALTER TABLE task ADD CONSTRAINT task_employee_pay_check CHECK (employee_pay_snapshot >= 0)');
$this->addSql("COMMENT ON CONSTRAINT task_employee_pay_check ON task IS 'Оплата исполнителю неотрицательная. Ноль допустим у внутренних работ'");

$this->addSql('ALTER TABLE task ADD CONSTRAINT task_client_price_check CHECK (client_price_snapshot >= 0)');
$this->addSql("COMMENT ON CONSTRAINT task_client_price_check ON task IS 'Цена для клиента неотрицательная. Пусто у внутренних работ'");

$this->addSql("ALTER TABLE task ADD CONSTRAINT task_status_shift_check CHECK (
    (status = 'new' AND shift_id IS NULL)
 OR (status IN ('assigned', 'in_progress', 'submitted', 'accepted') AND shift_id IS NOT NULL)
 OR status = 'cancelled'
)");
$this->addSql("COMMENT ON CONSTRAINT task_status_shift_check ON task IS 'Смена соответствует статусу: в new её нет, от assigned до accepted есть, у отменённой может быть любое значение'");
```

## task_checklist_item

```php
$this->addSql("COMMENT ON INDEX task_checklist_item_by_task_position IS 'Чек-лист задачи в порядке позиций. Уникальности нет: сервис перенумеровывает пункты подряд при сохранении списка'");
$this->addSql("COMMENT ON INDEX IDX_85C256568DB60186 IS 'Пункты чек-листа задачи. Нужен для CASCADE при удалении задачи'");
$this->addSql("COMMENT ON INDEX IDX_85C256562199DB86 IS 'Пункты, отмеченные пользователем. Нужен для проверки RESTRICT'");

$this->addSql('ALTER TABLE task_checklist_item ADD CONSTRAINT task_checklist_item_position_check CHECK (position > 0)');
$this->addSql("COMMENT ON CONSTRAINT task_checklist_item_position_check ON task_checklist_item IS 'Позиция начинается с единицы'");

$this->addSql('ALTER TABLE task_checklist_item ADD CONSTRAINT task_checklist_item_checked_pair_check CHECK ((checked_at IS NULL) = (checked_by_id IS NULL))');
$this->addSql("COMMENT ON CONSTRAINT task_checklist_item_checked_pair_check ON task_checklist_item IS 'Момент отметки и отметивший заполняются только вместе'");
```

## task_status_change

```php
$this->addSql("COMMENT ON INDEX task_status_change_history IS 'История задачи по времени. Основной индекс таблицы: по нему строится лента переходов'");
$this->addSql("COMMENT ON INDEX IDX_B80A9B3C8DB60186 IS 'Записи истории задачи. Нужен для CASCADE при удалении задачи'");
$this->addSql("COMMENT ON INDEX IDX_B80A9B3CBB70BC0E IS 'Переходы, сделанные в смене. Нужен для проверки RESTRICT при удалении смены'");
$this->addSql("COMMENT ON INDEX IDX_B80A9B3C828AD0A0 IS 'Переходы, сделанные пользователем. Нужен для проверки RESTRICT'");

$this->addSql("ALTER TABLE task_status_change ADD CONSTRAINT task_status_change_status_check CHECK (
    (from_status IS NULL OR from_status IN ('new', 'assigned', 'in_progress', 'submitted', 'accepted', 'cancelled'))
    AND to_status IN ('new', 'assigned', 'in_progress', 'submitted', 'accepted', 'cancelled')
    AND (from_status IS NULL OR from_status <> to_status)
)");
$this->addSql("COMMENT ON CONSTRAINT task_status_change_status_check ON task_status_change IS 'Список статусов задачи, дублирует PHP enum TaskStatus. Переход в тот же статус не записывается'");

$this->addSql("ALTER TABLE task_status_change ADD CONSTRAINT task_status_change_return_comment_check CHECK (
    NOT (from_status = 'submitted' AND to_status = 'in_progress')
    OR (comment IS NOT NULL AND btrim(comment) <> '')
)");
$this->addSql("COMMENT ON CONSTRAINT task_status_change_return_comment_check ON task_status_change IS 'Возврат на доработку только с непустым комментарием: иначе исполнитель не знает, что переделать'");
```

## ledger_entry

```php
$this->addSql("COMMENT ON INDEX ledger_entry_employee_history IS 'Журнал сотрудника по времени. Основной индекс таблицы: по нему строится выписка и сверка баланса'");
$this->addSql("COMMENT ON INDEX UNIQ_64272A69BB70BC0E IS 'Одна смена даёт не более одной записи: защита от двойного начисления почасовой'");
$this->addSql("COMMENT ON INDEX UNIQ_64272A698DB60186 IS 'Одна задача даёт не более одной записи: защита от двойного начисления за работу'");
$this->addSql("COMMENT ON INDEX IDX_64272A698C03F15C IS 'Записи сотрудника. Нужен для проверки RESTRICT при удалении сотрудника'");
$this->addSql("COMMENT ON INDEX IDX_64272A693E562840 IS 'Корректировки исходной записи. По ним считается, что сумма корректировок не больше исходной'");
$this->addSql("COMMENT ON INDEX IDX_64272A69B03A8386 IS 'Записи, созданные пользователем. Нужен для проверки RESTRICT'");

$this->addSql('ALTER TABLE ledger_entry ADD CONSTRAINT ledger_entry_amount_check CHECK (amount > 0)');
$this->addSql("COMMENT ON CONSTRAINT ledger_entry_amount_check ON ledger_entry IS 'Сумма строго больше нуля. Направление задаёт тип записи, запись на ноль не создаётся'");

$this->addSql("ALTER TABLE ledger_entry ADD CONSTRAINT ledger_entry_type_check CHECK (type IN ('shift', 'task', 'bonus', 'penalty', 'payout', 'correction_plus', 'correction_minus'))");
$this->addSql("COMMENT ON CONSTRAINT ledger_entry_type_check ON ledger_entry IS 'Список типов начислений, дублирует PHP enum LedgerEntryType'");

$this->addSql("ALTER TABLE ledger_entry ADD CONSTRAINT ledger_entry_source_check CHECK (
    (type = 'shift' AND shift_id IS NOT NULL AND task_id IS NULL)
 OR (type = 'task'  AND task_id  IS NOT NULL AND shift_id IS NULL)
 OR (type IN ('bonus', 'penalty', 'payout', 'correction_plus', 'correction_minus')
     AND shift_id IS NULL AND task_id IS NULL)
)");
$this->addSql("COMMENT ON CONSTRAINT ledger_entry_source_check ON ledger_entry IS 'Источник соответствует типу: у почасовой смена, у работы задача, у остальных источника нет'");

$this->addSql("ALTER TABLE ledger_entry ADD CONSTRAINT ledger_entry_correction_check CHECK (
    corrected_entry_id IS NULL
 OR (type IN ('correction_plus', 'correction_minus') AND corrected_entry_id <> id)
)");
$this->addSql("COMMENT ON CONSTRAINT ledger_entry_correction_check ON ledger_entry IS 'Ссылка на исправляемую запись только у корректировок, и запись не исправляет сама себя'");

$this->addSql("ALTER TABLE ledger_entry ADD CONSTRAINT ledger_entry_comment_check CHECK (
    type IN ('shift', 'task') OR (comment IS NOT NULL AND btrim(comment) <> '')
)");
$this->addSql("COMMENT ON CONSTRAINT ledger_entry_comment_check ON ledger_entry IS 'Ручные записи и корректировки только с непустым основанием. Автоматические начисления за смену и задачу комментария не требуют'");
```

---

## Имена, которые должны быть в IGNORED

```php
private const array IGNORED = [
    // индексы по выражению
    'location_name_lower_uniq' => true,
    'position_name_lower_uniq' => true,
    'service_type_name_lower_uniq' => true,
    'department_location_name_active_uniq' => true, // выражение + частичность
    // частичные индексы
    'employee_assignment_active_uniq' => true,
    'shift_employee_active_uniq' => true,
    'work_object_identifier_active_uniq' => true,
    'work_order_open_by_location' => true,
    'task_active_by_location' => true,
];
```

## Соглашение об именах индексов

- Уникальный индекс по всей таблице — суффикс `_uniq`: `location_name_lower_uniq`, `work_order_product_uniq`.
- Уникальный частичный (только среди действующих строк) — суффикс `_active_uniq`: `shift_employee_active_uniq`, `employee_assignment_active_uniq`.
- Неуникальный — без суффикса, имя говорит, для чего индекс: `task_active_by_location`, `ledger_entry_employee_history`.
- Индексы, которые Doctrine создаёт сама на внешние ключи, остаются с её именами `IDX_…`/`UNIQ_…`: задать им своё имя нельзя.
