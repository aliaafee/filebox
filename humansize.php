<?php
// Returns a size in a human-readable form from a byte count.
function humanSize($bytes)
{
    if ($bytes < 1024) return "$bytes Bytes";

    $units = array('KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');

    foreach ($units as $i => $unit)
    {
        // The reason for this threshold is to avoid e.g., "1000 KB",
        // instead jumping from e.g., "999 KB" to "0.97 MB".
        $multiplier = pow(1024, $i + 1);
        $threshold = $multiplier * 1000;

        if ($bytes < $threshold)
        {
            $size = formatToMinimumDigits($bytes / $multiplier, false);
            return "$size $unit";
        }
    }
}

// Efficiently calculates how many digits the integer portion of a number has.
function digits($number)
{
    // Yes, I could convert to string and count the characters,
    // but this is faster and cooler.
    $log = log10($number);
    if ($log < 0) return 1;
    return floor($log) + 1;
}

// Formats a number to a minimum amount of digits.
// In other words, makes sure that a number has at least $digits on it, even if
// that means introducing redundant decimal zeroes at the end, or rounding the
// ones present exceeding the $digits count when combined with the integers.
// For example:
//     formatToMinimumDigits(10)           // 10.0
//     formatToMinimumDigits(1.1)          // 1.10
//     formatToMinimumDigits(12.34)        // 12.3
//     formatToMinimumDigits(1.234)        // 1.23
//     formatToMinimumDigits(1.203)        // 1.20
//     formatToMinimumDigits(123.4)        // 123
//     formatToMinimumDigits(100)          // 100
//     formatToMinimumDigits(1000)         // 1000
//     formatToMinimumDigits(1)            // 1.00
//     formatToMinimumDigits(1.002)        // 1.00
//     formatToMinimumDigits(1.005)        // 1.01
//     formatToMinimumDigits(1.005, false) // 1.00
// This is primarily useful for generating human-friendly numbers.
function formatToMinimumDigits($value, $round = true, $digits = 3)
{
    $integers = floor($value);

    $decimalsNeeded = $digits - digits($integers);

    if ($decimalsNeeded < 1)
    {
        return $integers;
    }
    else
    {
        if ($round)
        {
            // This relies on implicit type casting of float to string.
            $parts = explode('.', round($value, $decimalsNeeded));
            // We re-declare the integers because they may change
            // after we round the number.
            $integers = $parts[0];
        }
        else
        {
            // Again, implicit type cast to string.
            $parts = explode('.', $value);
        }

        // And because of the implicit type cast, we must guard against
        // 1.00 becoming 1, thus not exploding the second half of it.
        $decimals = isset($parts[1]) ? $parts[1] : '0';
        $joined = "$integers.$decimals".str_repeat('0', $digits);
        return substr($joined, 0, $digits + 1);
    }
}
?>
