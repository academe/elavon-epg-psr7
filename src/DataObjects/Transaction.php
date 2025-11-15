<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\DataObjects;

use Academe\Elavon\Epg\Psr7\Enums\TransactionState;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\ValueObjects\Money;

/**
 * Transaction data transfer object.
 *
 * Represents a payment transaction for requests and responses.
 */
class Transaction
{
    public readonly Money $total;
    public readonly ?Card $card;

    /**
     * @param Money|array{amount: string, currencyCode: string} $total Transaction total amount
     * @param Card|array<string, mixed>|null $card Card details (required for card transactions)
     * @param string|null $id Transaction ID - readOnly, from responses
     * @param TransactionState|null $state Transaction state - readOnly, from responses
     * @param string|null $description Optional transaction description
     * @param string|null $customReference Optional merchant reference
     * @param string|null $createdAt Creation timestamp - readOnly, from responses
     *
     * @throws InvalidArgumentException When validation fails
     */
    public function __construct(
        Money|array $total,
        Card|array|null $card = null,
        public readonly ?string $id = null,
        public readonly ?TransactionState $state = null,
        public readonly ?string $description = null,
        public readonly ?string $customReference = null,
        public readonly ?string $createdAt = null,
    ) {
        // Normalize Money (accept both Money object or array)
        $this->total = $total instanceof Money
            ? $total
            : Money::fromArray($total);

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

        // Parse total (required)
        if (!isset($data['total'])) {
            throw new InvalidArgumentException('Missing required field: total');
        }

        return new self(
            total: is_array($data['total']) ? $data['total'] : Money::fromArray($data['total']),
            card: isset($data['card']) ? $data['card'] : null,
            id: isset($data['id']) ? (string) $data['id'] : null,
            state: $state,
            description: isset($data['description']) ? (string) $data['description'] : null,
            customReference: isset($data['customReference']) ? (string) $data['customReference'] : null,
            createdAt: isset($data['createdAt']) ? (string) $data['createdAt'] : null,
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
        $data = [
            'total' => $this->total->toArray(),
        ];

        if ($this->card !== null) {
            $data['card'] = $this->card->toArray();
        }

        if ($this->id !== null) {
            $data['id'] = $this->id;
        }

        if ($this->state !== null) {
            $data['state'] = $this->state->value;
        }

        if ($this->description !== null) {
            $data['description'] = $this->description;
        }

        if ($this->customReference !== null) {
            $data['customReference'] = $this->customReference;
        }

        if ($this->createdAt !== null) {
            $data['createdAt'] = $this->createdAt;
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
        // Transaction total must be positive
        if (!$this->total->isPositive()) {
            throw new InvalidArgumentException(
                'Transaction total must be positive'
            );
        }

        // For card transactions, card details are required
        // Note: In the full implementation, this might depend on transaction type
        // For now, we're building the credit card payment vertical
    }
}