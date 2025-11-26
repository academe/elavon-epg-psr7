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

    /**
     * Get property type definitions for this DTO.
     *
     * @return array<string, array<string>>
     */
    public static function getPropertyTypes(): array
    {
        return [
            'money' => ['total', 'initialTotal', 'surchargeTotal', 'surchargeInitialTotal'],
            'string' => ['rate'],
        ];
    }

    /**
     * @param Money|null $total Plan total amount after adding surcharge
     * @param Money|null $initialTotal Plan initial amount after adding surcharge
     * @param string|null $rate The surcharge rate (e.g., "0.035" means 3.5%)
     * @param Money|null $surchargeTotal Plan surcharge amount on total
     * @param Money|null $surchargeInitialTotal Plan surcharge amount on initial total
     */
    public function __construct(
        public readonly ?Money $total = null,
        public readonly ?Money $initialTotal = null,
        public readonly ?string $rate = null,
        public readonly ?Money $surchargeTotal = null,
        public readonly ?Money $surchargeInitialTotal = null,
    ) {
    }
}
