<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\DataObjects;

use Academe\Elavon\Epg\Psr7\DataObjects\Contact;
use Academe\Elavon\Epg\Psr7\DataObjects\Failure;
use Academe\Elavon\Epg\Psr7\DataObjects\ShopperStatement;
use Academe\Elavon\Epg\Psr7\DataObjects\Surcharge;
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
use Academe\Elavon\Epg\Psr7\ValueObjects\Money;

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
class Transaction
{
    // Normalized properties (objects)
    public readonly ?Money $total;
    public readonly ?Money $totalRefunded;
    public readonly ?Money $issuerTotal;
    public readonly ?Money $tip;
    public readonly ?Money $salesTax;
    public readonly ?Card $card;
    public readonly ?ShopperStatement $shopperStatement;
    public readonly ?Contact $shipTo;
    public readonly ?Contact $billTo;
    public readonly ?Surcharge $surcharge;
    /** @var array<Failure>|null */
    public readonly ?array $failures;

    /**
     * @param Money|array{amount: string, currencyCode: string}|null $total Transaction total
     * @param Money|array{amount: string, currencyCode: string}|null $totalRefunded [Response] Sum of all refunds
     * @param Money|array{amount: string, currencyCode: string}|null $issuerTotal [Response] Total in target currency
     * @param Money|array{amount: string, currencyCode: string}|null $tip [Response] Tip amount
     * @param Money|array{amount: string, currencyCode: string}|null $salesTax Sales tax amount
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
     * @param string|null $shopperEmailAddress [Response] Shopper's email
     * @param string|null $shopperIpAddress [Response] Shopper's IP address
     * @param string|null $shopperLanguageTag [Response] Shopper's IETF language tag
     * @param string|null $shopperTimeZone [Response] Shopper's time zone
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
        Money|array|null $total = null,
        Money|array|null $totalRefunded = null,
        Money|array|null $issuerTotal = null,
        Money|array|null $tip = null,
        Money|array|null $salesTax = null,
        Card|array|null $card = null,
        ShopperStatement|array|null $shopperStatement = null,
        Contact|array|null $shipTo = null,
        Contact|array|null $billTo = null,
        Surcharge|array|null $surcharge = null,
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
        public readonly ?string $shopperEmailAddress = null,
        public readonly ?string $shopperIpAddress = null,
        public readonly ?string $shopperLanguageTag = null,
        public readonly ?string $shopperTimeZone = null,

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
        // Normalize Money objects (accept both Money object or array)
        $this->total = match (true) {
            $total instanceof Money => $total,
            is_array($total) => Money::fromArray($total),
            default => null,
        };

        $this->totalRefunded = match (true) {
            $totalRefunded instanceof Money => $totalRefunded,
            is_array($totalRefunded) => Money::fromArray($totalRefunded),
            default => null,
        };

        $this->issuerTotal = match (true) {
            $issuerTotal instanceof Money => $issuerTotal,
            is_array($issuerTotal) => Money::fromArray($issuerTotal),
            default => null,
        };

        $this->tip = match (true) {
            $tip instanceof Money => $tip,
            is_array($tip) => Money::fromArray($tip),
            default => null,
        };

        $this->salesTax = match (true) {
            $salesTax instanceof Money => $salesTax,
            is_array($salesTax) => Money::fromArray($salesTax),
            default => null,
        };

        // Normalize Card (accept Card object, array, or null)
        $this->card = match (true) {
            $card instanceof Card => $card,
            is_array($card) => Card::fromArray($card),
            default => null,
        };

        // Normalize ShopperStatement
        $this->shopperStatement = match (true) {
            $shopperStatement instanceof ShopperStatement => $shopperStatement,
            is_array($shopperStatement) => ShopperStatement::fromArray($shopperStatement),
            default => null,
        };

        // Normalize Contact objects
        $this->shipTo = match (true) {
            $shipTo instanceof Contact => $shipTo,
            is_array($shipTo) => Contact::fromArray($shipTo),
            default => null,
        };

        $this->billTo = match (true) {
            $billTo instanceof Contact => $billTo,
            is_array($billTo) => Contact::fromArray($billTo),
            default => null,
        };

        // Normalize Surcharge
        $this->surcharge = match (true) {
            $surcharge instanceof Surcharge => $surcharge,
            is_array($surcharge) => Surcharge::fromArray($surcharge),
            default => null,
        };

        // Normalize failures array - convert array of arrays to array of Failure objects
        if ($failures !== null) {
            $this->failures = array_map(
                fn($failureData) => $failureData instanceof Failure
                    ? $failureData
                    : Failure::fromArray($failureData),
                $failures
            );
        } else {
            $this->failures = null;
        }

        $this->validate();
    }

    /**
     * Creates a Transaction instance from an array representation.
     *
     * @param array<string, mixed> $data Array with transaction data
     *
     * @throws InvalidArgumentException When data is invalid
     */
    public static function fromArray(array $data): self
    {
        // Parse state if present
        $state = null;
        if (isset($data['state'])) {
            $state = TransactionState::tryFrom($data['state']);
            if ($state === null) {
                throw new InvalidArgumentException("Invalid transaction state: {$data['state']}");
            }
        }

        // Parse type if present
        $type = null;
        if (isset($data['type'])) {
            $type = TransactionType::tryFrom($data['type']);
            if ($type === null) {
                throw new InvalidArgumentException("Invalid transaction type: {$data['type']}");
            }
        }

        // Parse processorDirective if present
        $processorDirective = null;
        if (isset($data['processorDirective'])) {
            $processorDirective = ProcessorDirective::tryFrom($data['processorDirective']);
            if ($processorDirective === null) {
                throw new InvalidArgumentException("Invalid processor directive: {$data['processorDirective']}");
            }
        }

        // Parse source if present
        $source = null;
        if (isset($data['source'])) {
            $source = Source::tryFrom($data['source']);
            if ($source === null) {
                throw new InvalidArgumentException("Invalid source: {$data['source']}");
            }
        }

        // Parse markupRateAnnotation if present
        $markupRateAnnotation = null;
        if (isset($data['markupRateAnnotation'])) {
            $markupRateAnnotation = MarkupRateAnnotation::tryFrom($data['markupRateAnnotation']);
            if ($markupRateAnnotation === null) {
                throw new InvalidArgumentException("Invalid markup rate annotation: {$data['markupRateAnnotation']}");
            }
        }

        // Parse paymentMethod if present
        $paymentMethod = null;
        if (isset($data['paymentMethod'])) {
            $paymentMethod = PaymentMethod::tryFrom($data['paymentMethod']);
            if ($paymentMethod === null) {
                throw new InvalidArgumentException("Invalid payment method: {$data['paymentMethod']}");
            }
        }

        // Parse paymentMethodOrigin if present
        $paymentMethodOrigin = null;
        if (isset($data['paymentMethodOrigin'])) {
            $paymentMethodOrigin = PaymentMethodOrigin::tryFrom($data['paymentMethodOrigin']);
            if ($paymentMethodOrigin === null) {
                throw new InvalidArgumentException("Invalid payment method origin: {$data['paymentMethodOrigin']}");
            }
        }

        // Parse paymentMethodQualifier if present
        $paymentMethodQualifier = null;
        if (isset($data['paymentMethodQualifier'])) {
            $paymentMethodQualifier = PaymentMethodQualifier::tryFrom($data['paymentMethodQualifier']);
            if ($paymentMethodQualifier === null) {
                throw new InvalidArgumentException("Invalid payment method qualifier: {$data['paymentMethodQualifier']}");
            }
        }

        // Parse marketSegment if present
        $marketSegment = null;
        if (isset($data['marketSegment'])) {
            $marketSegment = MarketSegment::tryFrom($data['marketSegment']);
            if ($marketSegment === null) {
                throw new InvalidArgumentException("Invalid market segment: {$data['marketSegment']}");
            }
        }

        // Parse shopperInteraction if present
        $shopperInteraction = null;
        if (isset($data['shopperInteraction'])) {
            $shopperInteraction = ShopperInteraction::tryFrom($data['shopperInteraction']);
            if ($shopperInteraction === null) {
                throw new InvalidArgumentException("Invalid shopper interaction: {$data['shopperInteraction']}");
            }
        }

        return new self(
            total: $data['total'] ?? null,
            totalRefunded: $data['totalRefunded'] ?? null,
            issuerTotal: $data['issuerTotal'] ?? null,
            tip: $data['tip'] ?? null,
            salesTax: $data['salesTax'] ?? null,
            card: $data['card'] ?? null,
            shopperStatement: $data['shopperStatement'] ?? null,
            shipTo: $data['shipTo'] ?? null,
            billTo: $data['billTo'] ?? null,
            surcharge: $data['surcharge'] ?? null,
            failures: $data['failures'] ?? null,
            id: isset($data['id']) ? (string) $data['id'] : null,
            state: $state,
            type: $type,
            description: isset($data['description']) ? (string) $data['description'] : null,
            customReference: isset($data['customReference']) ? (string) $data['customReference'] : null,
            createdAt: isset($data['createdAt']) ? (string) $data['createdAt'] : null,
            modifiedAt: isset($data['modifiedAt']) ? (string) $data['modifiedAt'] : null,
            authorizationExpiresAt: isset($data['authorizationExpiresAt']) ? (string) $data['authorizationExpiresAt'] : null,
            refundableUntil: isset($data['refundableUntil']) ? (string) $data['refundableUntil'] : null,
            href: isset($data['href']) ? (string) $data['href'] : null,
            merchant: isset($data['merchant']) ? (string) $data['merchant'] : null,
            processorAccount: isset($data['processorAccount']) ? (string) $data['processorAccount'] : null,
            account: isset($data['account']) ? (string) $data['account'] : null,
            terminal: isset($data['terminal']) ? (string) $data['terminal'] : null,
            forexAdvice: isset($data['forexAdvice']) ? (string) $data['forexAdvice'] : null,
            shopper: isset($data['shopper']) ? (string) $data['shopper'] : null,
            order: isset($data['order']) ? (string) $data['order'] : null,
            invoiceNumber: isset($data['invoiceNumber']) ? (string) $data['invoiceNumber'] : null,
            orderReference: isset($data['orderReference']) ? (string) $data['orderReference'] : null,
            shopperReference: isset($data['shopperReference']) ? (string) $data['shopperReference'] : null,
            purchaserReference: isset($data['purchaserReference']) ? (string) $data['purchaserReference'] : null,
            processorReference: isset($data['processorReference']) ? (string) $data['processorReference'] : null,
            issuerReference: isset($data['issuerReference']) ? (string) $data['issuerReference'] : null,
            shopperEmailAddress: isset($data['shopperEmailAddress']) ? (string) $data['shopperEmailAddress'] : null,
            shopperIpAddress: isset($data['shopperIpAddress']) ? (string) $data['shopperIpAddress'] : null,
            shopperLanguageTag: isset($data['shopperLanguageTag']) ? (string) $data['shopperLanguageTag'] : null,
            shopperTimeZone: isset($data['shopperTimeZone']) ? (string) $data['shopperTimeZone'] : null,
            shippingDate: isset($data['shippingDate']) ? (string) $data['shippingDate'] : null,
            credentialOnFileData: isset($data['credentialOnFileData']) ? (string) $data['credentialOnFileData'] : null,
            parentTransaction: isset($data['parentTransaction']) ? (string) $data['parentTransaction'] : null,
            hostedCard: isset($data['hostedCard']) ? (string) $data['hostedCard'] : null,
            hsmCard: isset($data['hsmCard']) ? (string) $data['hsmCard'] : null,
            storedCard: isset($data['storedCard']) ? (string) $data['storedCard'] : null,
            paymentLink: isset($data['paymentLink']) ? (string) $data['paymentLink'] : null,
            paymentSession: isset($data['paymentSession']) ? (string) $data['paymentSession'] : null,
            batch: isset($data['batch']) ? (string) $data['batch'] : null,
            manualBatch: isset($data['manualBatch']) ? (string) $data['manualBatch'] : null,
            processorBatchReference: isset($data['processorBatchReference']) ? (string) $data['processorBatchReference'] : null,
            conversionRate: isset($data['conversionRate']) ? (string) $data['conversionRate'] : null,
            markupRate: isset($data['markupRate']) ? (string) $data['markupRate'] : null,
            markupRateAnnotation: $markupRateAnnotation,
            rateProviderName: isset($data['rateProviderName']) ? (string) $data['rateProviderName'] : null,
            processorDirective: $processorDirective,
            source: $source,
            paymentMethod: $paymentMethod,
            paymentMethodOrigin: $paymentMethodOrigin,
            paymentMethodQualifier: $paymentMethodQualifier,
            marketSegment: $marketSegment,
            shopperInteraction: $shopperInteraction,
            isAuthorized: isset($data['isAuthorized']) ? (bool) $data['isAuthorized'] : null,
            isVoided: isset($data['isVoided']) ? (bool) $data['isVoided'] : null,
            isRefunded: isset($data['isRefunded']) ? (bool) $data['isRefunded'] : null,
            isReversed: isset($data['isReversed']) ? (bool) $data['isReversed'] : null,
            isCaptured: isset($data['isCaptured']) ? (bool) $data['isCaptured'] : null,
            isSettled: isset($data['isSettled']) ? (bool) $data['isSettled'] : null,
            isPartiallyRefunded: isset($data['isPartiallyRefunded']) ? (bool) $data['isPartiallyRefunded'] : null,
        );
    }

    /**
     * Converts the Transaction to an array representation.
     *
     * Only includes non-null values for cleaner JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];

        // Add all non-null Money properties
        if ($this->total !== null) {
            $data['total'] = $this->total->toArray();
        }

        if ($this->totalRefunded !== null) {
            $data['totalRefunded'] = $this->totalRefunded->toArray();
        }

        if ($this->issuerTotal !== null) {
            $data['issuerTotal'] = $this->issuerTotal->toArray();
        }

        if ($this->tip !== null) {
            $data['tip'] = $this->tip->toArray();
        }

        if ($this->salesTax !== null) {
            $data['salesTax'] = $this->salesTax->toArray();
        }

        // Add Card if not null
        if ($this->card !== null) {
            $data['card'] = $this->card->toArray();
        }

        // Add ShopperStatement if not null
        if ($this->shopperStatement !== null) {
            $data['shopperStatement'] = $this->shopperStatement->toArray();
        }

        // Add Contact objects if not null
        if ($this->shipTo !== null) {
            $data['shipTo'] = $this->shipTo->toArray();
        }

        if ($this->billTo !== null) {
            $data['billTo'] = $this->billTo->toArray();
        }

        // Add Surcharge if not null
        if ($this->surcharge !== null) {
            $data['surcharge'] = $this->surcharge->toArray();
        }

        // Add failures array if not null
        if ($this->failures !== null) {
            $data['failures'] = array_map(
                fn(Failure $failure) => $failure->toArray(),
                $this->failures
            );
        }

        // Simple string properties - add if not null
        $stringProperties = [
            'id', 'description', 'customReference',
            'createdAt', 'modifiedAt', 'authorizationExpiresAt', 'refundableUntil',
            'href', 'merchant', 'processorAccount', 'account', 'terminal', 'forexAdvice', 'shopper', 'order',
            'invoiceNumber', 'orderReference', 'shopperReference', 'purchaserReference',
            'processorReference', 'issuerReference',
            'shopperEmailAddress', 'shopperIpAddress', 'shopperLanguageTag', 'shopperTimeZone',
            'shippingDate', 'credentialOnFileData',
            'parentTransaction', 'hostedCard', 'hsmCard', 'storedCard',
            'paymentLink', 'paymentSession',
            'batch', 'manualBatch', 'processorBatchReference',
            'conversionRate', 'markupRate', 'rateProviderName',
        ];

        foreach ($stringProperties as $prop) {
            if ($this->$prop !== null) {
                $data[$prop] = $this->$prop;
            }
        }

        // Enum properties - convert to string values
        if ($this->state !== null) {
            $data['state'] = $this->state->value;
        }

        if ($this->type !== null) {
            $data['type'] = $this->type->value;
        }

        if ($this->processorDirective !== null) {
            $data['processorDirective'] = $this->processorDirective->value;
        }

        if ($this->source !== null) {
            $data['source'] = $this->source->value;
        }

        if ($this->markupRateAnnotation !== null) {
            $data['markupRateAnnotation'] = $this->markupRateAnnotation->value;
        }

        if ($this->paymentMethod !== null) {
            $data['paymentMethod'] = $this->paymentMethod->value;
        }

        if ($this->paymentMethodOrigin !== null) {
            $data['paymentMethodOrigin'] = $this->paymentMethodOrigin->value;
        }

        if ($this->paymentMethodQualifier !== null) {
            $data['paymentMethodQualifier'] = $this->paymentMethodQualifier->value;
        }

        if ($this->marketSegment !== null) {
            $data['marketSegment'] = $this->marketSegment->value;
        }

        if ($this->shopperInteraction !== null) {
            $data['shopperInteraction'] = $this->shopperInteraction->value;
        }

        // Boolean properties
        $booleanProperties = [
            'isAuthorized', 'isVoided', 'isRefunded', 'isReversed',
            'isCaptured', 'isSettled', 'isPartiallyRefunded',
        ];

        foreach ($booleanProperties as $prop) {
            if ($this->$prop !== null) {
                $data[$prop] = $this->$prop;
            }
        }

        return $data;
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