## Users

```php
$this->addSql("ALTER TABLE users ADD CONSTRAINT users_email_lower_check CHECK (email = lower(email))");
$this->addSql("COMMENT ON CONSTRAINT users_email_lower_check ON users IS 'Email хранится только в нижнем регистре'");
$this->addSql("ALTER TABLE users ADD CONSTRAINT users_gender_check CHECK (gender IN ('m', 'f'))");
$this->addSql("COMMENT ON CONSTRAINT users_gender_check ON users IS 'Gender может быть только (m, f) - male, female'");
```

## Employee

```php
$this->addSql("ALTER TABLE employee ADD CONSTRAINT employee_fired_at_check CHECK (fired_at IS NULL OR (fired_at >= hired_at AND fired_at <= CURRENT_DATE))");
$this->addSql("COMMENT ON CONSTRAINT employee_fired_at_check ON employee IS 'Дата увольнения не раньше даты найма и не в будущем'");
```

## Location

```php
$this->addSql('CREATE UNIQUE INDEX location_name_lower_uniq ON location (lower(name))');
$this->addSql("COMMENT ON INDEX location_name_lower_uniq IS 'Имя точки уникально без учёта регистра и не переиспользуется после закрытия'");
$this->addSql('ALTER TABLE location ADD CONSTRAINT location_closed_at_check CHECK (closed_at <= CURRENT_DATE)');
$this->addSql("COMMENT ON CONSTRAINT location_closed_at_check ON location IS 'Дата закрытия не в будущем'");
```

## Department

```php
$this->addSql('CREATE UNIQUE INDEX department_location_name_active ON department (location_id, lower(name)) WHERE closed_at IS NULL');
$this->addSql("COMMENT ON INDEX department_location_name_active IS 'Имя отдела уникально в точке среди незакрытых; закрытый отдел освобождает имя'");
$this->addSql('ALTER TABLE department ADD CONSTRAINT department_closed_at_check CHECK (closed_at <= CURRENT_DATE)');
$this->addSql("COMMENT ON CONSTRAINT department_closed_at_check ON department IS 'Дата закрытия не в будущем'");
```

## Position
```php
$this->addSql('CREATE UNIQUE INDEX position_name_lower_uniq ON position (lower(name))');
$this->addSql("COMMENT ON INDEX position_name_lower_uniq IS 'Название должности уникально без учёта регистра, архивация имя не освобождает'");
$this->addSql('ALTER TABLE position ADD CONSTRAINT position_archived_at_check CHECK (archived_at <= CURRENT_DATE)');
$this->addSql("COMMENT ON CONSTRAINT position_archived_at_check ON position IS 'Дата архивации не в будущем'");
```
