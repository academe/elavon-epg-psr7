<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Payment Link Status enumeration.
 *
 * The status of a payment link or payment method link.
 */
enum PaymentLinkStatus: string
{
    /** Payment link is active and can be used. */
    case ACTIVE = 'active';

    /** Payment link has been completed. */
    case COMPLETED = 'completed';

    /** Payment link has been cancelled. */
    case CANCELLED = 'cancelled';

    /** Payment link has expired. */
    case EXPIRED = 'expired';
}
