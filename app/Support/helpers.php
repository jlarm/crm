<?php

declare(strict_types=1);

function format_phone(string $state): string
{
    $state = preg_replace('/[^0-9]/', '', $state);

    if (mb_strlen($state) === 10) {
        return sprintf(
            '(%s) %s-%s',
            mb_substr($state, 0, 3),
            mb_substr($state, 3, 3),
            mb_substr($state, 6, 4),
        );
    }

    return $state;
}
