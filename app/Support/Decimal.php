<?php

namespace App\\Support;

use InvalidArgumentException;

final class Decimal
{
    /**
     * Convert a decimal amount to 8-decimal fixed-point units.
     * Extra fractional zeroes from SQL DECIMAL columns are accepted;
     * non-zero precision beyond 8 places is rejected rather than rounded.
     */
    public static function units(string|int $value): int
    {
        $value = trim((string) $value);

        if (!preg_match('/^([0-9]+)(?:\\.([0-9]+))?$/', $value, $matches)) {
            throw new InvalidArgumentException('Invalid decimal amount.');
        }

        $whole = ltrim($matches[1], '0');
        $whole = $whole === '' ? '0' : $whole;
        $fraction = $matches[2] ?? '';

        if (strlen($whole) > 10) {
            throw new InvalidArgumentException('Decimal amount exceeds the supported range.');
        }

        if (strlen($fraction) > 8) {
            $extra = substr($fraction, 8);
            if (trim($extra, '0') !== '') {
                throw new InvalidArgumentException('Decimal amount has unsupported precision.');
            }
            $fraction = substr($fraction, 0, 8);
        }

        $fraction = str_pad($fraction, 8, '0');
        return ((int) $whole * 100000000) + (int) $fraction;
    }

    public static function fromUnits(int $units): string
    {
        if ($units < 0) {
            throw new InvalidArgumentException('Decimal units cannot be negative.');
        }

        return sprintf('%d.%08d', intdiv($units, 100000000), $units % 100000000);
    }

    public static function compare(string $left, string $right): int
    {
        return self::units($left) <=> self::units($right);
    }

    public static function multiplyRatio(string $amount, int $numerator, int $denominator): string
    {
        if ($numerator < 0 || $denominator <= 0) {
            throw new InvalidArgumentException('Invalid decimal ratio.');
        }

        return self::fromUnits(intdiv(self::units($amount) * $numerator, $denominator));
    }
}
