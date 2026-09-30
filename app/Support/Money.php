<?php

namespace App\Support;

class Money
{
    /**
     * Indian rupees with lakh/crore grouping: 1234567.5 -> "₹12,34,568", or with decimals "₹12,34,567.50".
     * Whole amounts never show ".00".
     */
    public static function inr(float|int|string|null $amount, int $decimals = 0): string
    {
        $value = round((float) $amount, $decimals);
        $negative = $value < 0;

        if ($decimals > 0 && floor(abs($value)) == abs($value)) {
            $decimals = 0;
        }

        [$whole, $fraction] = array_pad(explode('.', number_format(abs($value), $decimals, '.', '')), 2, '');

        $last3 = substr($whole, -3);
        $rest = substr($whole, 0, -3);
        if ($rest !== '') {
            $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest).',';
        }

        return ($negative ? '-' : '').'₹'.$rest.$last3.($fraction !== '' ? '.'.$fraction : '');
    }
}
