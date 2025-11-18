<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Market segment enumeration.
 *
 * Indicates the market segment for the transaction.
 */
enum MarketSegment: string
{
    case RETAIL = 'retail';
    case RESTAURANT = 'restaurant';
}
