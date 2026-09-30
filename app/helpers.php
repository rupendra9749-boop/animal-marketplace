<?php

use App\Support\Money;

if (! function_exists('inr')) {
    /** Format an amount as Indian rupees, e.g. inr(1250000) -> "₹12,50,000". */
    function inr(float|int|string|null $amount, int $decimals = 0): string
    {
        return Money::inr($amount, $decimals);
    }
}
