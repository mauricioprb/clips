<?php

declare(strict_types=1);

namespace App\Enums;

enum EntrySource: string
{
    case Manual = 'manual';
    case Schedule = 'schedule';
    case Activity = 'activity';
}
