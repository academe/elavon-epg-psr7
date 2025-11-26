<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\MarkupRateAnnotation;
use Academe\Elavon\Epg\Psr7\Enums\ShopperInteraction;
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

    // Normalized enum properties
    public readonly ?MarkupRateAnnotation $markupRateAnnotation;
    public readonly ?ShopperInteraction $shopperInteraction;

    /**
     * Get property type definitions for this DTO.
     *
     * @return array<string, array<string>>
     */
    public static function getPropertyTypes(): array
    {
        return [
            'money' => ['total', 'issuerTotal'],
            'string' => [
                'href', 'id', 'createdAt', 'expiresAt', 'merchant', 'processorAccount',
                'account', 'storedCard', 'hostedCard', 'hsmCard', 'cardNumber',
                'panToken', 'maskedNumber', 'last4', 'bin', 'panFingerprint',
                'conversionRate', 'markupRate', 'rateProviderName', 'customReference',
            ],
            'array' => ['customFields'],
            'enum' => ['markupRateAnnotation', 'shopperInteraction'],
        ];
    }

    /**
     * @param string|null $href ForexAdvice Resource URL (self link)
     * @param string|null $id ForexAdvice Resource ID
     * @param string|null $createdAt Creation timestamp
     * @param string|null $expiresAt Expiration timestamp
     * @param string|null $merchant Merchant Resource URL
     * @param string|null $processorAccount ProcessorAccount Resource URL
     * @param string|null $account Account Resource URL
     * @param string|null $storedCard StoredCard Resource URL
     * @param string|null $hostedCard HostedCard Resource URL
     * @param string|null $hsmCard HsmCard Resource URL
     * @param string|null $cardNumber Personal account number (PAN)
     * @param string|null $panToken PAN token
     * @param string|null $maskedNumber Masked card number
     * @param string|null $last4 Last 4 digits of card
     * @param string|null $bin Bank identification number
     * @param string|null $panFingerprint PAN fingerprint
     * @param Money|null $total Transaction total in merchant currency
     * @param Money|null $issuerTotal Transaction total in card issuer currency
     * @param string|null $conversionRate Conversion rate between currencies
     * @param string|null $markupRate Markup rate applied
     * @param MarkupRateAnnotation|string|null $markupRateAnnotation Markup rate annotation
     * @param string|null $rateProviderName Rate provider name
     * @param ShopperInteraction|string|null $shopperInteraction Shopper interaction type
     * @param string|null $customReference Custom reference
     * @param array<string, mixed>|null $customFields Custom fields
     */
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
        MarkupRateAnnotation|string|null $markupRateAnnotation = null,
        public readonly ?string $rateProviderName = null,
        ShopperInteraction|string|null $shopperInteraction = null,
        public readonly ?string $customReference = null,
        public readonly ?array $customFields = null,
    ) {
        // Normalize enum objects
        $this->markupRateAnnotation = match (true) {
            $markupRateAnnotation instanceof MarkupRateAnnotation => $markupRateAnnotation,
            is_string($markupRateAnnotation) => MarkupRateAnnotation::from($markupRateAnnotation),
            default => null,
        };

        $this->shopperInteraction = match (true) {
            $shopperInteraction instanceof ShopperInteraction => $shopperInteraction,
            is_string($shopperInteraction) => ShopperInteraction::from($shopperInteraction),
            default => null,
        };
    }
}
