<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Card brand enumeration.
 *
 * Helps distinguish different brands for the same scheme.
 */
enum CardBrand: string
{
    case AMERICAN_EXPRESS = 'American Express';
    case UNION_PAY_CREDIT = 'UnionPay Credit';
    case UNION_PAY_DEBIT = 'UnionPay Debit';
    case DINERS_CLUB = 'Diners Club';
    case DISCOVER = 'Discover';
    case JCB = 'JCB';
    case MAESTRO = 'Maestro';
    case MASTERCARD = 'MasterCard';
    case MASTERCARD_CREDIT = 'MasterCard Credit';
    case MASTERCARD_DEBIT = 'MasterCard Debit';
    case VISA = 'Visa';
    case VISA_DEBIT = 'Visa Debit';
    case VISA_CREDIT = 'Visa Credit';
    case VISA_ELECTRON = 'Visa Electron';
}
