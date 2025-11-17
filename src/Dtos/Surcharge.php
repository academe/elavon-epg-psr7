<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\ValueObjects\Money;

/**
 * Surcharge data transfer object.
 *
 * Surcharge information if surchargeAdvice or refundSurchargeAdvice was created for the transaction.
 * All properties are read-only.
 */
class Surcharge implements DataTransferObject
{
    use SerializesData;

    // Normalized Money properties
    public readonly ?Money $unadjustedTotal;
    public readonly ?Money $unadjustedRefundableTotal;
    public readonly ?Money $surchargeTotal;

    /**
     * Get property type definitions for this DTO.
     *
     * @return array<string, array<string>>
     */
    public static function getPropertyTypes(): array
    {
        return [
            'object' => ['unadjustedTotal', 'unadjustedRefundableTotal', 'surchargeTotal'],
            'string' => ['rate'],
        ];
    }

    /**
     * @param Money|array{amount: string, currencyCode: string}|null $unadjustedTotal Transaction total before adding surcharge
     * @param Money|array{amount: string, currencyCode: string}|null $unadjustedRefundableTotal Amount of original unadjusted transaction still available for refund
     * @param Money|array{amount: string, currencyCode: string}|null $surchargeTotal Surcharge total
     * @param string|null $rate Merchant's surcharge rate (e.g., "0.035" = 3.5%)
     */
    public function __construct(
        Money|array|null $unadjustedTotal = null,
        Money|array|null $unadjustedRefundableTotal = null,
        Money|array|null $surchargeTotal = null,
        public readonly ?string $rate = null,
    ) {
        // Normalize Money objects
        $this->unadjustedTotal = match (true) {
            $unadjustedTotal instanceof Money => $unadjustedTotal,
            is_array($unadjustedTotal) => Money::fromData($unadjustedTotal),
            default => null,
        };

        $this->unadjustedRefundableTotal = match (true) {
            $unadjustedRefundableTotal instanceof Money => $unadjustedRefundableTotal,
            is_array($unadjustedRefundableTotal) => Money::fromData($unadjustedRefundableTotal),
            default => null,
        };

        $this->surchargeTotal = match (true) {
            $surchargeTotal instanceof Money => $surchargeTotal,
            is_array($surchargeTotal) => Money::fromData($surchargeTotal),
            default => null,
        };
    }

}
