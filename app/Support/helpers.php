<?php

declare(strict_types=1);

/**
 * Formats a US phone number as 999-999-9999, dropping a leading country code 1.
 * Anything that isn't a 10-digit number comes back as its digits.
 */
function format_phone(string $state): string
{
    $state = preg_replace('/[^0-9]/', '', $state) ?? '';

    if (mb_strlen($state) === 11 && str_starts_with($state, '1')) {
        $state = mb_substr($state, 1);
    }

    if (mb_strlen($state) === 10) {
        return sprintf(
            '%s-%s-%s',
            mb_substr($state, 0, 3),
            mb_substr($state, 3, 3),
            mb_substr($state, 6, 4),
        );
    }

    return $state;
}
