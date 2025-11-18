<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Shopper interaction enumeration.
 *
 * The type of interaction with the shopper that generated this transaction.
 */
enum ShopperInteraction: string
{
    /** The shopper generated this transaction with an ecommerce interaction. */
    case ECOMMERCE = 'ecommerce';

    /** The shopper generated this transaction with a mail order. */
    case MAIL_ORDER = 'mailOrder';

    /** The shopper generated this transaction with a telephone order. */
    case TELEPHONE_ORDER = 'telephoneOrder';

    /** A merchant generated this transaction without shopper involvement. */
    case MERCHANT_INITIATED = 'merchantInitiated';

    /** The shopper generated the transaction in-person with a physical payment method (e.g. POS terminal). */
    case IN_PERSON = 'inPerson';
}
