<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\ValueObjects\CustomFields;
use Money\Money;

/**
 * Refund Surcharge Advice data transfer object.
 *
 * Surcharge calculation advice for refund transactions.
 * All properties are read-only.
 */
class RefundSurchargeAdvice implements DataTransferObject
{
    use SerializesData;

    public function __construct(
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $expiresAt = null,
        public readonly ?string $merchant = null,
        public readonly ?string $processorAccount = null,
        public readonly ?string $parentTransaction = null,
        public readonly ?Money $total = null,
        public readonly ?string $surchargeRate = null,
        public readonly ?Money $surchargeTotal = null,
        public readonly ?Money $adjustedTotal = null,
        public readonly ?string $customReference = null,
        public readonly ?CustomFields $customFields = null,
    ) {
    }
}
