<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\ValueObjects\Money;

/**
 * Subscription Surcharge data transfer object.
 *
 * Surcharge information if surchargeAdvice was created for the subscription.
 * All properties are read-only and returned in API responses.
 */
class SubscriptionSurcharge implements DataTransferObject
{
    use SerializesData;

    // Normalized properties (objects)
    public readonly ?Money $total;
    public readonly ?Money $initialTotal;
    public readonly ?Money $surchargeTotal;
    public readonly ?Money $surchargeInitialTotal;

    /**
     * Get property type definitions for this DTO.
     *
     * @return array<string, array<string>>
     */
    public static function getPropertyTypes(): array
    {
        return [
            'object' => ['total', 'initialTotal', 'surchargeTotal', 'surchargeInitialTotal'],
            'string' => ['rate'],
        ];
    }

    /**
     * @param Money|array{amount: string, currencyCode: string}|null $total Plan total amount after adding surcharge
     * @param Money|array{amount: string, currencyCode: string}|null $initialTotal Plan initial amount after adding surcharge
     * @param string|null $rate The surcharge rate (e.g., "0.035" means 3.5%)
     * @param Money|array{amount: string, currencyCode: string}|null $surchargeTotal Plan surcharge amount on total
     * @param Money|array{amount: string, currencyCode: string}|null $surchargeInitialTotal Plan surcharge amount on initial total
     */
    public function __construct(
        Money|array|null $total = null,
        Money|array|null $initialTotal = null,
        public readonly ?string $rate = null,
        Money|array|null $surchargeTotal = null,
        Money|array|null $surchargeInitialTotal = null,
    ) {
        // Normalize Money objects
        $this->total = match (true) {
            $total instanceof Money => $total,
            is_array($total) => Money::fromData($total),
            default => null,
        };

        $this->initialTotal = match (true) {
            $initialTotal instanceof Money => $initialTotal,
            is_array($initialTotal) => Money::fromData($initialTotal),
            default => null,
        };

        $this->surchargeTotal = match (true) {
            $surchargeTotal instanceof Money => $surchargeTotal,
            is_array($surchargeTotal) => Money::fromData($surchargeTotal),
            default => null,
        };

        $this->surchargeInitialTotal = match (true) {
            $surchargeInitialTotal instanceof Money => $surchargeInitialTotal,
            is_array($surchargeInitialTotal) => Money::fromData($surchargeInitialTotal),
            default => null,
        };
    }
}
