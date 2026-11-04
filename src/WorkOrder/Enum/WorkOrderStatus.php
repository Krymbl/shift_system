<?php

declare(strict_types=1);

namespace App\WorkOrder\Enum;

enum WorkOrderStatus: string
{
    case OPEN = 'open';
    case CLOSED = 'closed';
    case CANCELLED = 'cancelled';

    public function getTitle(): string
    {
        return match ($this) {
            self::OPEN => 'Открыт',
            self::CLOSED => 'Закрыт',
            self::CANCELLED => 'Отменен',
        };
    }
}
