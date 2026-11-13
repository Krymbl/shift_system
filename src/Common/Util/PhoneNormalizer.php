<?php

declare(strict_types=1);

namespace App\Common\Util;

final class PhoneNormalizer
{
    public static function normalize(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);

        if ($digits === '') {
            return null;
        }

        $digits = match (true) {
            strlen($digits) === 11 && str_starts_with($digits, '8') => '7' . substr($digits, 1),
            strlen($digits) === 10 && str_starts_with($digits, '9') => '7' . $digits,
            default => $digits,
        };

        return '+' . $digits;
    }
}
