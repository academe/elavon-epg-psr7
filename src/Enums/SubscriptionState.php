<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Subscription State enumeration.
 *
 * Represents the current state of a subscription.
 */
enum SubscriptionState: string
{
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case UNPAID = 'unpaid';
    case PAST_DUE = 'pastDue';
    case UNKNOWN = 'unknown';
}
