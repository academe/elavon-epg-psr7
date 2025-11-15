<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Transaction state enumeration.
 *
 * Represents the current state of a transaction in the EPG system.
 */
enum TransactionState: string
{
    case AUTHORIZATION_PENDING = 'authorizationPending';
    case AUTHORIZED = 'authorized';
    case DECLINED = 'declined';
    case CAPTURED = 'captured';
    case SETTLED = 'settled';
    case REFUNDED = 'refunded';
    case VOIDED = 'voided';
    case FAILED = 'failed';
    case UNKNOWN = 'unknown';
}