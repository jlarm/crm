<?php

function format_phone (string $state): string
{
    $state = preg_replace('/[^0-9]/', '', $state);

    if (strlen($state) === 10) {
        return sprintf(
            '(%s) %s-%s',
            substr($state, 0, 3),
            substr($state, 3, 3),
            substr($state, 6, 4),
        );
    }

    return $state;
}
