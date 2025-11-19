<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Resource Type enum.
 *
 * The type of resource affected by a notification or event.
 */
enum ResourceType: string
{
    case MERCHANT = 'merchant';
    case PROCESSOR_ACCOUNT = 'processorAccount';
    case ACCOUNT = 'account';
    case PLAN_LIST = 'planList';
    case ORDER = 'order';
    case PAYMENT_LINK = 'paymentLink';
    case PAYMENT_SESSION = 'paymentSession';
    case HOSTED_CARD = 'hostedCard';
    case STORED_CARD = 'storedCard';
    case FOREX_ADVICE = 'forexAdvice';
    case TRANSACTION = 'transaction';
    case BATCH = 'batch';
    case SHOPPER = 'shopper';
    case PLAN = 'plan';
    case SUBSCRIPTION = 'subscription';
    case SURCHARGE_ADVICE = 'surchargeAdvice';
    case REFUND_SURCHARGE_ADVICE = 'refundSurchargeAdvice';
    case NOTIFICATION = 'notification';
    case GOOGLE_PAY_PAYMENT = 'googlePayPayment';
    case APPLE_PAY_PAYMENT = 'applePayPayment';
    case APPLE_PAY_PAYMENT_SESSION = 'applePayPaymentSession';
    case TOTAL_ADJUSTMENT = 'totalAdjustment';
    case UNKNOWN = 'unknown';
}
