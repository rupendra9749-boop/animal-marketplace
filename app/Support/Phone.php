<?php

namespace App\Support;

/** Indian phone numbers: people type them many ways (+91 98765 43210, 098765-43210, 9876543210). */
class Phone
{
    /** People typing on a Hindi keyboard may use Devanagari digits (०१२...): turn them into 0123... first. */
    public static function latinDigits(string $input): string
    {
        return strtr($input, ['०' => '0', '१' => '1', '२' => '2', '३' => '3', '४' => '4', '५' => '5', '६' => '6', '७' => '7', '८' => '8', '९' => '9']);
    }

    /**
     * A valid Indian mobile number as "+91 XXXXX XXXXX", or null when it is not one.
     */
    public static function mobile(?string $input): ?string
    {
        $digits = preg_replace('/\D+/', '', self::latinDigits((string) $input));

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        if (! preg_match('/^[6-9]\d{9}$/', $digits)) {
            return null;
        }

        return '+91 '.substr($digits, 0, 5).' '.substr($digits, 5);
    }

    /**
     * A mobile number (formatted as above) or a landline with its STD code ("0161 2345678"), which clinics
     * often use. Anything else is rejected.
     */
    public static function contact(?string $input): ?string
    {
        if ($mobile = self::mobile($input)) {
            return $mobile;
        }

        $digits = preg_replace('/\D+/', '', self::latinDigits((string) $input));

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = '0'.substr($digits, 2);
        }

        // 0 + STD code (2-4 digits) + number (6-8 digits) = 10 to 12 digits
        return preg_match('/^0[1-9]\d{9,10}$/', $digits) ? $digits : null;
    }

    /** Digits with the country code, ready for a wa.me link; null when there is no usable number. */
    public static function whatsapp(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === '') {
            return null;
        }

        return strlen($digits) === 10 ? '91'.$digits : ltrim($digits, '0');
    }

    /** Digits only, for tel: links (mobile numbers get their +91). */
    public static function forDialing(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        return str_starts_with((string) $phone, '+') ? '+'.$digits : $digits;
    }
}
