<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Card scheme (network) enumeration.
 *
 * Represents the payment card network/scheme.
 */
enum CardScheme: string
{
    case AMERICAN_EXPRESS = 'American Express';
    case DINERS_CLUB = 'Diners Club';
    case DISCOVER = 'Discover';
    case JCB = 'JCB';
    case MAESTRO = 'Maestro';
    case MASTERCARD = 'MasterCard';
    case UNION_PAY = 'UnionPay';
    case VISA = 'Visa';
    case UNKNOWN = 'Unknown';
}