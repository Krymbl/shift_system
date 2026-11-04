<?php

declare(strict_types=1);

namespace App\Task\Enum;

enum TaskStatus: string
{
    case NEW = 'new';
    case ASSIGNED = 'assigned';
    case IN_PROGRESS = 'in_progress';
    case SUBMITTED = 'submitted';
    case ACCEPTED = 'accepted';
    case CANCELLED = 'cancelled';

    public function getTitle(): string
    {
        return match ($this) {
            self::NEW => 'Новая',
            self::ASSIGNED => 'Назначена',
            self::IN_PROGRESS => 'В работе',
            self::SUBMITTED => 'Сдана',
            self::ACCEPTED => 'Принята',
            self::CANCELLED => 'Отменена',
        };
    }
}
