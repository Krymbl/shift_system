<?php

declare(strict_types=1);

namespace App\Identity\Enum;

enum Role: string
{
    case USER = 'ROLE_USER';
    case CLIENT = 'ROLE_CLIENT';
    case EMPLOYEE = 'ROLE_EMPLOYEE';
    case ADMIN = 'ROLE_ADMIN';
    case SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    public function getTitle(): string
    {
        return match ($this) {
            self::USER => 'Пользователь',
            self::CLIENT => 'Клиент',
            self::EMPLOYEE => 'Сотрудник',
            self::ADMIN => 'Администратор',
            self::SUPER_ADMIN => 'Супер-админ',
        };
    }
}
