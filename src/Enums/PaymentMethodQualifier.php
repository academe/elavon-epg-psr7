<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Payment method qualifier enumeration.
 *
 * Indicates whether the payment method is credit or debit.
 */
enum PaymentMethodQualifier: string
{
    case CREDIT = 'credit';
    case DEBIT = 'debit';
}
