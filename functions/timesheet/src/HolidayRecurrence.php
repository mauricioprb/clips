<?php

declare(strict_types=1);

namespace Clips;

enum HolidayRecurrence: string
{
    case Once = 'once';
    case Yearly = 'yearly';
    case CorpusChristi = 'corpus_christi';
}
