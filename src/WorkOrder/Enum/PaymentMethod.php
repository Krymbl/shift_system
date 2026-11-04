<?php

declare(strict_types=1);

namespace App\WorkOrder\Enum;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case CARD = 'card';
    case TRANSFER = 'transfer';

    public function getTitle(): string
    {
        return match ($this) {
            self::CASH => 'Наличные',
            self::CARD => 'Карта',
            self::TRANSFER => 'Перевод',
        };
    }
}
