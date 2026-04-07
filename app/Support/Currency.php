<?php

namespace App\Support;

class Currency
{
    public static function format(int|float|string|null $amount): string
    {
        if ($amount === null || $amount === '') {
            return '--';
        }

        return '₱' . number_format((float) $amount, 2);
    }
}
