<?php

declare(strict_types=1);

namespace App\Catalog\Enum;

enum ProductUnit: string
{
    case PCS = 'pcs';
    case L = 'l';
    case M = 'm';
    case KG = 'kg';

    public function getTitle(): string
    {
        return match ($this) {
            self::PCS => 'шт',
            self::L => 'л',
            self::M => 'м',
            self::KG => 'кг',
        };
    }
}
