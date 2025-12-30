<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * True / False / Unknown Enum.
 *
 * Represents a tri-state boolean value that can be true, false, or unknown.
 */
enum TrueFalseOrUnknown: string
{
    case TRUE = 'true';
    case FALSE = 'false';
    case UNKNOWN = 'unknown';
}