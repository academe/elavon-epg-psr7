<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Do Auto Capture After Time Unit Enum.
 *
 * Time unit for auto-capture configuration. Can only be "day" or "hour".
 */
enum DoAutoCaptureAfterTimeUnit: string
{
    case HOUR = 'hour';
    case DAY = 'day';
}