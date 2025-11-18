<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Markup rate annotation enumeration.
 *
 * Indicates whether the markup rate is above, below, or equal to the European Central Bank rate.
 */
enum MarkupRateAnnotation: string
{
    case NONE = 'none';
    case ABOVE_ECB = 'aboveEcb';
    case BELOW_ECB = 'belowEcb';
}
