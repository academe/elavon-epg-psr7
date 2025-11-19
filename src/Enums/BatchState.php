<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Batch state enumeration.
 *
 * Represents the current state of a settlement batch.
 */
enum BatchState: string
{
    case SUBMITTED = 'submitted';
    case SETTLED = 'settled';
    case REJECTED = 'rejected';
    case FAILED = 'failed';
    case UNKNOWN = 'unknown';
}
