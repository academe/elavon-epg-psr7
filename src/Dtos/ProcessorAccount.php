<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\CardBrand;
use Academe\Elavon\Epg\Psr7\Enums\MarketSegment;
use Academe\Elavon\Epg\Psr7\Enums\PaymentMethod;
use Academe\Elavon\Epg\Psr7\Enums\PaymentMethodOrigin;
use Academe\Elavon\Epg\Psr7\Enums\Region;

/**
 * ProcessorAccount data transfer object.
 *
 * A merchant may have multiple processor accounts, although Elavon Payment Gateway
 * currently only supports one. Each processor account tracks and processes transactions
 * separately.
 *
 * All properties are read-only and only present in API responses.
 */
class ProcessorAccount implements DataTransferObject
{
    use SerializesData;

    /** @var array<CardBrand>|null */
    public readonly ?array $supportedCardBrands;

    /** @var array<PaymentMethod>|null */
    public readonly ?array $supportedPaymentMethods;

    /** @var array<PaymentMethodOrigin>|null */
    public readonly ?array $supportedPaymentMethodOrigins;

    /**
     * Get property type definitions for this DTO.
     *
     * @return array<string, array<string>>
     */
    public static function getPropertyTypes(): array
    {
        return [
            'object' => ['businessAddress'],
            'enum' => ['marketSegment', 'region'],
            'array' => [
                'supportedCardBrands',
                'supportedPaymentMethods',
                'supportedPaymentMethodOrigins',
                'pinlessDebit',
            ],
            'string' => [
                'href', 'id', 'merchant', 'processorReference', 'legalName',
                'friendlyName', 'tradeName', 'businessPhone', 'businessEmail',
                'businessWebsite', 'merchantCategoryCode', 'settlementCurrencyCode',
                'languageTag',
            ],
            'boolean' => [
                'isDccEnabled', 'isMccEnabled', 'isStandaloneRefundEnabled',
            ],
        ];
    }

    /**
     * @param CardBrand[] $supportedCardBrands
     * @param PaymentMethod[] $supportedPaymentMethods
     * @param PaymentMethodOrigin[] $supportedPaymentMethodOrigins
     */
    public function __construct(
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?string $merchant = null,
        public readonly ?string $processorReference = null,
        public readonly ?string $legalName = null,
        public readonly ?string $friendlyName = null,
        public readonly ?string $tradeName = null,
        public readonly ?Address $businessAddress = null,
        public readonly ?string $businessPhone = null,
        public readonly ?string $businessEmail = null,
        public readonly ?string $businessWebsite = null,
        public readonly ?string $merchantCategoryCode = null,
        public readonly ?MarketSegment $marketSegment = null,
        public readonly ?Region $region = null,
        public readonly ?string $settlementCurrencyCode = null,
        public readonly ?string $languageTag = null,
        array|null $supportedCardBrands = null,
        array|null $supportedPaymentMethods = null,
        array|null $supportedPaymentMethodOrigins = null,
        public readonly ?bool $isDccEnabled = null,
        public readonly ?bool $isMccEnabled = null,
        public readonly ?bool $isStandaloneRefundEnabled = null,
        public readonly ?array $pinlessDebit = null,
    ) {
        // Normalize CardBrand array
        $this->supportedCardBrands = $this->normalizeEnumArray(
            $supportedCardBrands,
            CardBrand::class
        );

        // Normalize PaymentMethod array
        $this->supportedPaymentMethods = $this->normalizeEnumArray(
            $supportedPaymentMethods,
            PaymentMethod::class
        );

        // Normalize PaymentMethodOrigin array
        $this->supportedPaymentMethodOrigins = $this->normalizeEnumArray(
            $supportedPaymentMethodOrigins,
            PaymentMethodOrigin::class
        );
    }
}
