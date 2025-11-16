<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Transaction type enumeration.
 *
 * Represents the type of transaction being performed.
 */
enum TransactionType: string
{
    case SALE = 'sale';
    case REFUND = 'refund';
    case VOID = 'void';
}
