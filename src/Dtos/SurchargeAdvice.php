<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\ValueObjects\CustomFields;
use DateTimeImmutable;
use Money\Money;

/**
 * Surcharge Advice data transfer object.
 *
 * Surcharge calculation advice for transactions.
 * All properties are read-only.
 */
class SurchargeAdvice implements DataTransferObject
{
    use SerializesData;

    public function __construct(
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $expiresAt = null,
        public readonly ?string $merchant = null,
        public readonly ?string $processorAccount = null,
        public readonly ?string $hsmCard = null,
        public readonly ?string $googlePayPayment = null,
        public readonly ?string $applePayPayment = null,
        public readonly ?string $pazePayment = null,
        public readonly ?string $panToken = null,
        public readonly ?string $maskedNumber = null,
        public readonly ?string $last4 = null,
        public readonly ?string $bin = null,
        public readonly ?string $panFingerprint = null,
        public readonly ?Money $total = null,
        public readonly ?string $surchargeRate = null,
        public readonly ?Money $surchargeTotal = null,
        public readonly ?Money $adjustedTotal = null,
        public readonly ?string $customReference = null,
        public readonly ?CustomFields $customFields = null,
    ) {
    }
}
