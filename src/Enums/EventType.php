<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Event Type enum.
 *
 * The type of event that triggered a notification.
 */
enum EventType: string
{
    case SALE_DECLINED = 'saleDeclined';
    case SALE_AUTHORIZED = 'saleAuthorized';
    case SALE_HELD_FOR_REVIEW = 'saleHeldForReview';
    case SALE_CAPTURED = 'saleCaptured';
    case SALE_SETTLED = 'saleSettled';
    case VOID_DECLINED = 'voidDeclined';
    case VOID_AUTHORIZED = 'voidAuthorized';
    case REFUND_DECLINED = 'refundDeclined';
    case REFUND_AUTHORIZED = 'refundAuthorized';
    case REFUND_CAPTURED = 'refundCaptured';
    case REFUND_SETTLED = 'refundSettled';
    case TOTAL_ADJUSTMENT_AUTHORIZED = 'totalAdjustmentAuthorized';
    case TOTAL_ADJUSTMENT_DECLINED = 'totalAdjustmentDeclined';
    case TRANSACTION_TOTAL_ADJUSTMENT = 'transactionTotalAdjustment';
    case UNKNOWN = 'unknown';
}
