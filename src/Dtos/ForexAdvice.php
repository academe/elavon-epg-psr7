<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\MarkupRateAnnotation;
use Academe\Elavon\Epg\Psr7\Enums\ShopperInteraction;
use Academe\Elavon\Epg\Psr7\ValueObjects\CustomFields;
use Money\Money;

/**
 * Forex Advice data transfer object.
 *
 * Foreign exchange conversion advice for cross-currency transactions.
 * All properties are read-only.
 */
class ForexAdvice implements DataTransferObject
{
    use SerializesData;

    public function __construct(
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $expiresAt = null,
        public readonly ?string $merchant = null,
        public readonly ?string $processorAccount = null,
        public readonly ?string $account = null,
        public readonly ?string $storedCard = null,
        public readonly ?string $hostedCard = null,
        public readonly ?string $hsmCard = null,
        public readonly ?string $cardNumber = null,
        public readonly ?string $panToken = null,
        public readonly ?string $maskedNumber = null,
        public readonly ?string $last4 = null,
        public readonly ?string $bin = null,
        public readonly ?string $panFingerprint = null,
        public readonly ?Money $total = null,
        public readonly ?Money $issuerTotal = null,
        public readonly ?string $conversionRate = null,
        public readonly ?string $markupRate = null,
        public readonly ?MarkupRateAnnotation $markupRateAnnotation = null,
        public readonly ?string $rateProviderName = null,
        public readonly ?ShopperInteraction $shopperInteraction = null,
        public readonly ?string $customReference = null,
        public readonly ?CustomFields $customFields = null,
    ) {
    }
}
