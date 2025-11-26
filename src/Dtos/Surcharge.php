<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Money\Money;

/**
 * Surcharge data transfer object.
 *
 * Surcharge information if surchargeAdvice or refundSurchargeAdvice was created for the transaction.
 * All properties are read-only.
 */
class Surcharge implements DataTransferObject
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
            'money' => ['unadjustedTotal', 'unadjustedRefundableTotal', 'surchargeTotal'],
            'string' => ['rate'],
        ];
    }

    /**
     * @param Money|null $unadjustedTotal Transaction total before adding surcharge
     * @param Money|null $unadjustedRefundableTotal Amount of original unadjusted transaction still available for refund
     * @param Money|null $surchargeTotal Surcharge total
     * @param string|null $rate Merchant's surcharge rate (e.g., "0.035" = 3.5%)
     */
    public function __construct(
        public readonly ?Money $unadjustedTotal = null,
        public readonly ?Money $unadjustedRefundableTotal = null,
        public readonly ?Money $surchargeTotal = null,
        public readonly ?string $rate = null,
    ) {
    }
}
