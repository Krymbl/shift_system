<?php

declare(strict_types=1);

namespace App\Shift\Enum;

enum ShiftStatus: string
{
    case REQUESTED = 'requested';
    case REJECTED = 'rejected';
    case OPEN = 'open';
    case CLOSED = 'closed';
    case FORCED_CLOSED = 'forced_closed';

    public function getTitle(): string
    {
        return match ($this) {
            self::REQUESTED => 'Запрошено',
            self::REJECTED => 'Отклонено',
            self::OPEN => 'Открыто',
            self::CLOSED => 'Закрыто',
            self::FORCED_CLOSED => 'Принудительно закрыто',
        };
    }
}
