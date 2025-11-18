<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Payment method enumeration.
 *
 * Represents the payment method type, such as a card issued by a bank or a local payment method.
 *
 * Note: Values are case-sensitive and must match the API specification exactly.
 * API uses PascalCase for 'Card' but UPPERCASE for 'BLIK' and 'ACH'.
 */
enum PaymentMethod: string
{
    case CARD = 'Card';
    case BLIK = 'BLIK';
    case ACH = 'ACH';
}
