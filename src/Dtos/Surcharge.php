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

    public function __construct(
        public readonly ?Money $unadjustedTotal = null,
        public readonly ?Money $unadjustedRefundableTotal = null,
        public readonly ?Money $surchargeTotal = null,
        public readonly ?string $rate = null,
    ) {
    }
}
