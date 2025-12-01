<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Money\Money;

/**
 * Subscription Surcharge data transfer object.
 *
 * Surcharge information if surchargeAdvice was created for the subscription.
 * All properties are read-only and returned in API responses.
 */
class SubscriptionSurcharge implements DataTransferObject
{
    use SerializesData;

    public function __construct(
        public readonly ?Money $total = null,
        public readonly ?Money $initialTotal = null,
        public readonly ?string $rate = null,
        public readonly ?Money $surchargeTotal = null,
        public readonly ?Money $surchargeInitialTotal = null,
    ) {
    }
}
