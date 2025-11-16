<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Processor directive enumeration.
 *
 * Indicates how the processor should handle the transaction.
 */
enum ProcessorDirective: string
{
    case NONE = 'none';
    case REVERSAL = 'reversal';
}
