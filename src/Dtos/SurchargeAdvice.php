<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
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

    /**
     * Get property type definitions for this DTO.
     *
     * @return array<string, array<string>>
     */
    public static function getPropertyTypes(): array
    {
        return [
            'money' => ['total', 'surchargeTotal', 'adjustedTotal'],
            'string' => [
                'href', 'id', 'createdAt', 'expiresAt', 'merchant', 'processorAccount',
                'hsmCard', 'googlePayPayment', 'applePayPayment', 'pazePayment',
                'panToken', 'maskedNumber', 'last4', 'bin', 'panFingerprint',
                'surchargeRate', 'customReference',
            ],
            'array' => ['customFields'],
        ];
    }

    public function __construct(
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $expiresAt = null,
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
        public readonly ?array $customFields = null,
    ) {
    }
}
