<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Dtos\Contact;
use Academe\Elavon\Epg\Psr7\Dtos\Failure;
use Academe\Elavon\Epg\Psr7\Dtos\ShopperStatement;
use Academe\Elavon\Epg\Psr7\Dtos\Surcharge;
use Academe\Elavon\Epg\Psr7\Enums\MarkupRateAnnotation;
use Academe\Elavon\Epg\Psr7\Enums\MarketSegment;
use Academe\Elavon\Epg\Psr7\Enums\PaymentMethod;
use Academe\Elavon\Epg\Psr7\Enums\PaymentMethodOrigin;
use Academe\Elavon\Epg\Psr7\Enums\PaymentMethodQualifier;
use Academe\Elavon\Epg\Psr7\Enums\ProcessorDirective;
use Academe\Elavon\Epg\Psr7\Enums\ShopperInteraction;
use Academe\Elavon\Epg\Psr7\Enums\Source;
use Academe\Elavon\Epg\Psr7\Enums\TransactionState;
use Academe\Elavon\Epg\Psr7\Enums\TransactionType;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\ValueObjects\EmailAddress;
use Academe\Elavon\Epg\Psr7\ValueObjects\IpAddress;
use Academe\Elavon\Epg\Psr7\ValueObjects\LanguageTag;
use Academe\Elavon\Epg\Psr7\ValueObjects\TimeZone;
use Money\Money;

/**
 * Transaction data transfer object.
 *
 * Represents a payment transaction for both requests and responses.
 * All properties are optional as different fields are used in different contexts.
 * Validation of required fields should occur at the message level (CreateTransactionRequest, etc.).
 *
 * Properties marked [Response] are typically only present in API responses.
 * Properties marked [Request] are typically sent in API requests.
 * Properties without markers may appear in both contexts.
 */
class Transaction implements DataTransferObject
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
            'money' => [
                'total', 'totalRefunded', 'issuerTotal', 'tip', 'salesTax',
            ],
            'object' => [
                'card', 'shopperStatement', 'shipTo', 'billTo', 'surcharge',
                'shopperEmailAddress', 'shopperIpAddress', 'shopperLanguageTag', 'shopperTimeZone',
            ],
            'array' => [
                'failures',
            ],
            'enum' => [
                'state', 'type', 'processorDirective', 'source', 'paymentMethod',
                'paymentMethodOrigin', 'paymentMethodQualifier', 'marketSegment',
                'shopperInteraction', 'markupRateAnnotation',
            ],
            'string' => [
                'id', 'description', 'customReference',
                'createdAt', 'modifiedAt', 'authorizationExpiresAt', 'refundableUntil',
                'href', 'merchant', 'processorAccount', 'account', 'terminal', 'forexAdvice', 'shopper', 'order',
                'invoiceNumber', 'orderReference', 'shopperReference', 'purchaserReference',
                'processorReference', 'issuerReference',
                'shippingDate', 'credentialOnFileData',
                'parentTransaction', 'hostedCard', 'hsmCard', 'storedCard',
                'paymentLink', 'paymentSession',
                'batch', 'manualBatch', 'processorBatchReference',
                'conversionRate', 'markupRate', 'rateProviderName',
            ],
            'boolean' => [
                'isAuthorized', 'isVoided', 'isRefunded', 'isReversed',
                'isCaptured', 'isSettled', 'isPartiallyRefunded',
            ],
        ];
    }

    // Normalized properties (objects)
    // public readonly ?Card $card;
    // public readonly ?ShopperStatement $shopperStatement;
    // public readonly ?Contact $shipTo;
    // public readonly ?Contact $billTo;
    // public readonly ?Surcharge $surcharge;
    public readonly ?EmailAddress $shopperEmailAddress;
    // public readonly ?IpAddress $shopperIpAddress;
    public readonly ?LanguageTag $shopperLanguageTag;
    public readonly ?TimeZone $shopperTimeZone;
    /** @var array<Failure>|null */
    public readonly ?array $failures;

    /**
     * @param Money|null $total Transaction total (Money\Money)
     * @param Money|null $totalRefunded [Response] Sum of all refunds (Money\Money)
     * @param Money|null $issuerTotal [Response] Total in target currency (Money\Money)
     * @param Money|null $tip [Response] Tip amount (Money\Money)
     * @param Money|null $salesTax Sales tax amount (Money\Money)
     * @param Card|array<string, mixed>|null $card Card details
     * @param ShopperStatement|array<string, mixed>|null $shopperStatement [Request] Dynamic statement overrides
     * @param Contact|array<string, mixed>|null $shipTo [Request] Shipping address
     * @param Contact|array<string, mixed>|null $billTo [Request] Billing address
     * @param Surcharge|array<string, mixed>|null $surcharge [Response] Surcharge information
     * @param array<array<string, mixed>>|null $failures [Response] Transaction failures
     * @param string|null $id [Response] Transaction ID
     * @param TransactionState|null $state [Response] Transaction state
     * @param TransactionType|null $type [Response] Transaction type (sale/refund/void)
     * @param string|null $description Transaction description
     * @param string|null $customReference [Request] Optional merchant reference
     * @param string|null $createdAt [Response] Creation timestamp
     * @param string|null $modifiedAt [Response] Modification timestamp
     * @param string|null $authorizationExpiresAt [Response] Authorization expiration
     * @param string|null $refundableUntil [Response] Refundable until timestamp
     * @param string|null $href [Response] Resource URL (self link)
     * @param string|null $merchant [Response] Merchant resource URL
     * @param string|null $processorAccount [Response] ProcessorAccount resource URL
     * @param string|null $account [Response] Account resource URL
     * @param string|null $terminal [Request/Response] Terminal resource URL
     * @param string|null $forexAdvice [Response] ForexAdvice resource URL
     * @param string|null $shopper [Response] Shopper resource URL
     * @param string|null $order [Response] Order resource URL
     * @param string|null $invoiceNumber [Request] Optional invoice number
     * @param string|null $orderReference [Response] Order reference
     * @param string|null $shopperReference [Response] Shopper reference (e.g., PO number)
     * @param string|null $purchaserReference [Response] Purchaser identifier
     * @param string|null $processorReference [Response] Processor-assigned reference
     * @param string|null $issuerReference [Response] Card issuer-assigned reference
     * @param EmailAddress|string|null $shopperEmailAddress [Response] Shopper's email
     * @param IpAddress|string|null $shopperIpAddress [Response] Shopper's IP address
     * @param LanguageTag|string|null $shopperLanguageTag [Response] Shopper's IETF language tag
     * @param TimeZone|string|null $shopperTimeZone [Response] Shopper's time zone
     * @param string|null $shippingDate [Request] Optional shipping date
     * @param string|null $credentialOnFileData [Request/Response] Credential on file data
     * @param string|null $parentTransaction [Response] Parent transaction URL
     * @param string|null $hostedCard [Request] HostedCard resource URL
     * @param string|null $hsmCard [Request] HsmCard resource URL
     * @param string|null $storedCard [Response] StoredCard resource URL
     * @param string|null $paymentLink [Response] PaymentLink resource URL
     * @param string|null $paymentSession [Response] PaymentSession resource URL
     * @param string|null $batch [Response] Batch resource URL
     * @param string|null $manualBatch [Response] ManualBatch resource URL
     * @param string|null $processorBatchReference [Response] Processor batch reference
     * @param string|null $conversionRate [Response] Currency exchange rate
     * @param string|null $markupRate [Response] Markup percent (e.g., "0.0399" = 3.99%)
     * @param MarkupRateAnnotation|null $markupRateAnnotation [Response] Markup rate annotation
     * @param string|null $rateProviderName [Response] Rate provider name
     * @param ProcessorDirective|null $processorDirective [Response] Processor directive
     * @param Source|null $source [Response] Transaction source
     * @param PaymentMethod|null $paymentMethod [Response] Payment method type
     * @param PaymentMethodOrigin|null $paymentMethodOrigin [Response] Payment method origin
     * @param PaymentMethodQualifier|null $paymentMethodQualifier [Response] Payment method qualifier
     * @param MarketSegment|null $marketSegment Market segment
     * @param ShopperInteraction|null $shopperInteraction [Response] Shopper interaction type
     * @param bool|null $isAuthorized [Response] Whether transaction was authorized
     * @param bool|null $isVoided [Response] Whether transaction was voided
     * @param bool|null $isRefunded [Response] Whether transaction was refunded
     * @param bool|null $isReversed [Response] Whether transaction was reversed
     * @param bool|null $isCaptured [Response] Whether transaction was captured
     * @param bool|null $isSettled [Response] Whether transaction was settled
     * @param bool|null $isPartiallyRefunded [Response] Whether transaction was partially refunded
     *
     * @throws InvalidArgumentException When validation fails
     */
    public function __construct(
        // Primary transaction data
        public readonly ?Money $total = null,
        public readonly ?Money $totalRefunded = null,
        public readonly ?Money $issuerTotal = null,
        public readonly ?Money $tip = null,
        public readonly ?Money $salesTax = null,
        public readonly ?Card $card = null,
        public readonly ?ShopperStatement $shopperStatement = null,
        public readonly ?Contact $shipTo = null,
        public readonly ?Contact $billTo = null,
        public readonly ?Surcharge $surcharge = null,
        ?array $failures = null,

        // Identity and state
        public readonly ?string $id = null,
        public readonly ?TransactionState $state = null,
        public readonly ?TransactionType $type = null,

        // Descriptive fields
        public readonly ?string $description = null,
        public readonly ?string $customReference = null,

        // Timestamps
        public readonly ?string $createdAt = null,
        public readonly ?string $modifiedAt = null,
        public readonly ?string $authorizationExpiresAt = null,
        public readonly ?string $refundableUntil = null,

        // Resource URLs
        public readonly ?string $href = null,
        public readonly ?string $merchant = null,
        public readonly ?string $processorAccount = null,
        public readonly ?string $account = null,
        public readonly ?string $terminal = null,
        public readonly ?string $forexAdvice = null,
        public readonly ?string $shopper = null,
        public readonly ?string $order = null,

        // Order and invoice details
        public readonly ?string $invoiceNumber = null,
        public readonly ?string $orderReference = null,
        public readonly ?string $shopperReference = null,
        public readonly ?string $purchaserReference = null,

        // Processing references
        public readonly ?string $processorReference = null,
        public readonly ?string $issuerReference = null,

        // Shopper information
        EmailAddress|string|null $shopperEmailAddress = null,
        public readonly ?IpAddress $shopperIpAddress = null,
        LanguageTag|string|null $shopperLanguageTag = null,
        TimeZone|string|null $shopperTimeZone = null,

        // Additional fields
        public readonly ?string $shippingDate = null,
        public readonly ?string $credentialOnFileData = null,

        // Related transactions/payments
        public readonly ?string $parentTransaction = null,
        public readonly ?string $hostedCard = null,
        public readonly ?string $hsmCard = null,
        public readonly ?string $storedCard = null,
        public readonly ?string $paymentLink = null,
        public readonly ?string $paymentSession = null,

        // Batch information
        public readonly ?string $batch = null,
        public readonly ?string $manualBatch = null,
        public readonly ?string $processorBatchReference = null,

        // Financial rates
        public readonly ?string $conversionRate = null,
        public readonly ?string $markupRate = null,
        public readonly ?MarkupRateAnnotation $markupRateAnnotation = null,
        public readonly ?string $rateProviderName = null,

        // Processing details
        public readonly ?ProcessorDirective $processorDirective = null,
        public readonly ?Source $source = null,
        public readonly ?PaymentMethod $paymentMethod = null,
        public readonly ?PaymentMethodOrigin $paymentMethodOrigin = null,
        public readonly ?PaymentMethodQualifier $paymentMethodQualifier = null,
        public readonly ?MarketSegment $marketSegment = null,
        public readonly ?ShopperInteraction $shopperInteraction = null,
        public readonly ?bool $isAuthorized = null,
        public readonly ?bool $isVoided = null,
        public readonly ?bool $isRefunded = null,
        public readonly ?bool $isReversed = null,
        public readonly ?bool $isCaptured = null,
        public readonly ?bool $isSettled = null,
        public readonly ?bool $isPartiallyRefunded = null,
    ) {
        // Normalize Card (accept Card object, array, or null)
        // $this->card = match (true) {
        //     $card instanceof Card => $card,
        //     is_array($card) => Card::fromData($card),
        //     default => null,
        // };

        // Normalize ShopperStatement
        // $this->shopperStatement = match (true) {
        //     $shopperStatement instanceof ShopperStatement => $shopperStatement,
        //     is_array($shopperStatement) => ShopperStatement::fromData($shopperStatement),
        //     default => null,
        // };

        // Normalize Contact objects
        // $this->shipTo = match (true) {
        //     $shipTo instanceof Contact => $shipTo,
        //     is_array($shipTo) => Contact::fromData($shipTo),
        //     default => null,
        // };

        // $this->billTo = match (true) {
        //     $billTo instanceof Contact => $billTo,
        //     is_array($billTo) => Contact::fromData($billTo),
        //     default => null,
        // };

        // Normalize Surcharge
        // $this->surcharge = match (true) {
        //     $surcharge instanceof Surcharge => $surcharge,
        //     is_array($surcharge) => Surcharge::fromData($surcharge),
        //     default => null,
        // };

        // Normalize EmailAddress
        $this->shopperEmailAddress = match (true) {
            $shopperEmailAddress instanceof EmailAddress => $shopperEmailAddress,
            is_string($shopperEmailAddress) => EmailAddress::fromData($shopperEmailAddress),
            default => null,
        };

        // Normalize IpAddress
        // $this->shopperIpAddress = match (true) {
        //     $shopperIpAddress instanceof IpAddress => $shopperIpAddress,
        //     is_string($shopperIpAddress) => IpAddress::fromData($shopperIpAddress),
        //     default => null,
        // };

        // Normalize LanguageTag
        $this->shopperLanguageTag = match (true) {
            $shopperLanguageTag instanceof LanguageTag => $shopperLanguageTag,
            is_string($shopperLanguageTag) => LanguageTag::fromData($shopperLanguageTag),
            default => null,
        };

        // Normalize TimeZone
        $this->shopperTimeZone = match (true) {
            $shopperTimeZone instanceof TimeZone => $shopperTimeZone,
            is_string($shopperTimeZone) => TimeZone::fromData($shopperTimeZone),
            default => null,
        };

        // Normalize failures array - convert array of arrays to array of Failure objects
        if ($failures !== null) {
            $this->failures = array_map(
                fn($failureData) => $failureData instanceof Failure
                    ? $failureData
                    : Failure::fromData($failureData),
                $failures
            );
        } else {
            $this->failures = null;
        }

        $this->validate();
    }

    /**
     * Validates transaction data.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // If total is present, it must be positive
        if ($this->total !== null && !$this->total->isPositive()) {
            throw new InvalidArgumentException(
                'Transaction total must be positive'
            );
        }

        // Note: Validation of required fields should occur at the message level
        // (e.g., CreateTransactionRequest) since requirements differ for requests vs responses
    }
}