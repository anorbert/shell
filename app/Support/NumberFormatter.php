<?php

namespace App\Support;

final class NumberFormatter
{
    public static function money(mixed $value): string
    {
        return self::format($value, 2);
    }

    public static function decimal(mixed $value, int $maxDecimals = 8): string
    {
        return self::format($value, max(0, min(12, $maxDecimals)));
    }

    private static function format(mixed $value, int $decimals): string
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return '0';
        }

        $formatted = number_format((float) $value, $decimals, '.', ',');
        if (str_contains($formatted, '.')) {
            $formatted = rtrim(rtrim($formatted, '0'), '.');
        }

        return $formatted === '-0' ? '0' : $formatted;
    }
}
