<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Verification Enum.
 *
 * Result of a test of a submitted field.
 */
enum Verification: string
{
    case MATCHED = 'matched';
    case UNMATCHED = 'unmatched';
    case UNPROVIDED = 'unprovided';
    case UNSUPPORTED = 'unsupported';
    case UNAVAILABLE = 'unavailable';
    case UNKNOWN = 'unknown';
}