<?php

declare(strict_types=1);

namespace App\Enum;

enum EmailDirection: string
{
    case Outbound = 'outbound';
    case Inbound = 'inbound';
}
