<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\DataObjects;

use Academe\Elavon\Epg\Psr7\Enums\ProcessorDirective;
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
    public readonly ?Card $card;

    /**
     * @param Money|array{amount: string, currencyCode: string}|null $total Transaction total
     * @param Card|array<string, mixed>|null $card Card details
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
     * @param ProcessorDirective|null $processorDirective [Response] Processor directive
     * @param Source|null $source [Response] Transaction source
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
        Card|array|null $card = null,

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

        // Processing details
        public readonly ?ProcessorDirective $processorDirective = null,
        public readonly ?Source $source = null,
        public readonly ?bool $isAuthorized = null,
        public readonly ?bool $isVoided = null,
        public readonly ?bool $isRefunded = null,
        public readonly ?bool $isReversed = null,
        public readonly ?bool $isCaptured = null,
        public readonly ?bool $isSettled = null,
        public readonly ?bool $isPartiallyRefunded = null,
    ) {
        // Normalize Money (accept both Money object or array)
        $this->total = match (true) {
            $total instanceof Money => $total,
            is_array($total) => Money::fromArray($total),
            default => null,
        };

        // Normalize Card (accept Card object, array, or null)
        $this->card = match (true) {
            $card instanceof Card => $card,
            is_array($card) => Card::fromArray($card),
            default => null,
        };

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

        return new self(
            total: $data['total'] ?? null,
            card: $data['card'] ?? null,
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
            processorDirective: $processorDirective,
            source: $source,
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

        // Add all non-null properties
        if ($this->total !== null) {
            $data['total'] = $this->total->toArray();
        }

        if ($this->card !== null) {
            $data['card'] = $this->card->toArray();
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