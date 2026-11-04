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
            LedgerEntryType::SHIFT => 'Смена',
            LedgerEntryType::TASK => 'Задача',
            LedgerEntryType::BONUS => 'Бонус',
            LedgerEntryType::PENALTY => 'Штраф',
            LedgerEntryType::PAYOUT => 'Вычет',
            LedgerEntryType::CORRECTION_PLUS => 'Корректировка положительная',
            LedgerEntryType::CORRECTION_MINUS => 'Корректировка отрицательная',
        };
    }
}
