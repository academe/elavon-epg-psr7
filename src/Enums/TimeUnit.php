<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Time Unit enumeration.
 *
 * Unit of time for billing intervals and other time-based operations.
 */
enum TimeUnit: string
{
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';
    case YEAR = 'year';
}
