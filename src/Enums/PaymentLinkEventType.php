<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Payment Link Event Type enumeration.
 *
 * Type of event for a payment link, such as making a payment or sending an email reminder.
 * Note: You can only create payment link events with the 'reminderSent' type.
 */
enum PaymentLinkEventType: string
{
    /** Payment was made on the payment link. */
    case PAYMENT = 'payment';

    /** Email reminder was sent to shopper. */
    case REMINDER_SENT = 'reminderSent';

    /** Unknown event type. */
    case UNKNOWN = 'unknown';
}
