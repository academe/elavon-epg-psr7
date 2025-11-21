<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Region enumeration.
 *
 * Represents the geographical region for payment processing.
 */
enum Region: string
{
    case EU = 'eu';
    case NA = 'na';
    case UNKNOWN = 'unknown';
}
