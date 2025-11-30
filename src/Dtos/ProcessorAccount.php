<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Attributes\ArrayOf;
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
        /** @var array<CardBrand>|null */
        #[ArrayOf(CardBrand::class)]
        public readonly ?array $supportedCardBrands = null,
        /** @var array<PaymentMethod>|null */
        #[ArrayOf(PaymentMethod::class)]
        public readonly ?array $supportedPaymentMethods = null,
        /** @var array<PaymentMethodOrigin>|null */
        #[ArrayOf(PaymentMethodOrigin::class)]
        public readonly ?array $supportedPaymentMethodOrigins = null,
        public readonly ?bool $isDccEnabled = null,
        public readonly ?bool $isMccEnabled = null,
        public readonly ?bool $isStandaloneRefundEnabled = null,
        /** @var array<PinlessDebitCardScheme>|null */
        #[ArrayOf(PinlessDebitCardScheme::class)]
        public readonly ?array $pinlessDebit = null,
    ) {
    }
}
