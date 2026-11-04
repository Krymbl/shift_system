<?php

declare(strict_types=1);

namespace App\Identity\Enum;

enum Gender: string
{
    case M = 'm';
    case F = 'f';

    public function getTitle(): string
    {
        return match ($this) {
            self::M => 'Мужчина',
            self::F => 'Женщина',
        };
    }
}
