<?php

declare(strict_types=1);

namespace App\Payroll\Enum;

enum LedgerEntryType: string
{
    case SHIFT = 'shift';
    case TASK = 'task';
    case BONUS = 'bonus';
    case PENALTY = 'penalty';
    case PAYOUT = 'payout';
    case CORRECTION_PLUS = 'correction_plus';
    case CORRECTION_MINUS = 'correction_minus';

    public function getTitle(): string
    {
        return match ($this) {
            self::SHIFT => 'Смена',
            self::TASK => 'Задача',
            self::BONUS => 'Бонус',
            self::PENALTY => 'Штраф',
            self::PAYOUT => 'Выплата',
            self::CORRECTION_PLUS => 'Корректировка положительная',
            self::CORRECTION_MINUS => 'Корректировка отрицательная',
        };
    }
}
