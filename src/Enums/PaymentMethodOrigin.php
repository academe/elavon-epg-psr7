<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Payment method origin enumeration.
 *
 * The origin of the payment method, which may differ from the payment method if,
 * for example, a third party wallet (e.g. Apple Pay) originated a card payment method.
 *
 * Note: These values contain spaces and mixed case. Use exact API values.
 */
enum PaymentMethodOrigin: string
{
    case CARD = 'Card';
    case APPLE_PAY = 'Apple Pay';
    case GOOGLE_PAY = 'Google Pay';
    case PAZE = 'Paze';
    case BLIK = 'BLIK';
    case POLISH_BANK_TRANSFER = 'Polish Bank Transfer';
    case ACH = 'ACH';
    case UNKNOWN_WALLET = 'Unknown Wallet';
}
