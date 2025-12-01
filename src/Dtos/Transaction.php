<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Attributes\ArrayOf;
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
        /** @var array<Failure>|null */
        #[ArrayOf(Failure::class)]
        public readonly ?array $failures = null,

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
        public readonly ?EmailAddress $shopperEmailAddress = null,
        public readonly ?IpAddress $shopperIpAddress = null,
        public readonly ?LanguageTag $shopperLanguageTag = null,
        public readonly ?TimeZone $shopperTimeZone = null,

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