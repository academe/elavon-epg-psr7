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

    // Normalized properties (objects)
    public readonly ?Address $businessAddress;
    public readonly ?MarketSegment $marketSegment;
    public readonly ?Region $region;

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
     * @param string|null $href ProcessorAccount Resource URL (self link)
     * @param string|null $id ProcessorAccount Resource ID assigned by server
     * @param string|null $merchant Merchant Resource URL
     * @param string|null $processorReference Reference assigned by the processor
     * @param string|null $legalName Legal name under which the merchant operates
     * @param string|null $friendlyName Friendly name assigned to processor account
     * @param string|null $tradeName Trading/DBA name
     * @param Address|array<string, mixed>|null $businessAddress Business address
     * @param string|null $businessPhone Business phone
     * @param string|null $businessEmail Business email
     * @param string|null $businessWebsite Business website
     * @param string|null $merchantCategoryCode Merchant category code (MCC)
     * @param MarketSegment|string|null $marketSegment Market segment
     * @param Region|string|null $region Region (e.g., NA, EU)
     * @param string|null $settlementCurrencyCode Settlement currency (ISO 4217)
     * @param string|null $languageTag Language tag
     * @param array<CardBrand|string>|null $supportedCardBrands Supported card brands
     * @param array<PaymentMethod|string>|null $supportedPaymentMethods Supported payment methods
     * @param array<PaymentMethodOrigin|string>|null $supportedPaymentMethodOrigins Supported payment method origins
     * @param bool|null $isDccEnabled Is DCC enabled?
     * @param bool|null $isMccEnabled Is MCC enabled?
     * @param bool|null $isStandaloneRefundEnabled Does this support standalone refund?
     * @param array<mixed>|null $pinlessDebit Pinless debit configuration
     */
    public function __construct(
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?string $merchant = null,
        public readonly ?string $processorReference = null,
        public readonly ?string $legalName = null,
        public readonly ?string $friendlyName = null,
        public readonly ?string $tradeName = null,
        Address|array|null $businessAddress = null,
        public readonly ?string $businessPhone = null,
        public readonly ?string $businessEmail = null,
        public readonly ?string $businessWebsite = null,
        public readonly ?string $merchantCategoryCode = null,
        MarketSegment|string|null $marketSegment = null,
        Region|string|null $region = null,
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
        // Normalize Address object
        $this->businessAddress = match (true) {
            $businessAddress instanceof Address => $businessAddress,
            is_array($businessAddress) => Address::fromData($businessAddress),
            default => null,
        };

        // Normalize MarketSegment enum
        $this->marketSegment = match (true) {
            $marketSegment instanceof MarketSegment => $marketSegment,
            is_string($marketSegment) => MarketSegment::from($marketSegment),
            default => null,
        };

        // Normalize Region enum
        $this->region = match (true) {
            $region instanceof Region => $region,
            is_string($region) => Region::from($region),
            default => null,
        };

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

    /**
     * Normalize an array of enum values.
     *
     * @template T of \BackedEnum
     * @param array<T|string>|null $items
     * @param class-string<T> $enumClass
     * @return array<T>|null
     */
    private function normalizeEnumArray(?array $items, string $enumClass): ?array
    {
        if ($items === null) {
            return null;
        }

        return array_map(
            fn ($item) => $item instanceof $enumClass ? $item : $enumClass::from($item),
            $items
        );
    }
}
