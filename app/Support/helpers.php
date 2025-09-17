<?php

declare(strict_types=1);

function format_phone(string $state): string
{
    $state = preg_replace('/[^0-9]/', '', $state);

    if (mb_strlen((string) $state) === 10) {
        return sprintf(
            '(%s) %s-%s',
            mb_substr((string) $state, 0, 3),
            mb_substr((string) $state, 3, 3),
            mb_substr((string) $state, 6, 4),
        );
    }

    return $state;
}
