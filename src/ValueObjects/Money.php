<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\ValueObjects;

use Academe\Elavon\Epg\Psr7\Contracts\ValueObject;
use Academe\Elavon\Epg\Psr7\Enums\Currency;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * Represents a monetary amount with currency (AmountAndCurrency in EPG API).
 *
 * This value object ensures that monetary amounts are always associated with
 * a currency and validates the amount format according to EPG API requirements.
 *
 * Amount format requirements:
 * - At most 9 integer digits
 * - At most 4 fractional digits
 * - Decimal separator is a period (.)
 * - No thousands separators
 * - Examples: "1.23", "99.99", "1000.00", "12345.6789"
 *
 * Note: Money implements ValueObject (not DataTransferObject) because it doesn't
 * need the property type system - it has a simple 2-property structure with custom
 * serialization logic and domain behavior methods.
 */
final class Money implements ValueObject
{
    /**
     * @param string $amount The monetary amount as a string (e.g., "99.99")
     * @param Currency $currency The ISO 4217 currency code
     *
     * @throws InvalidArgumentException When amount format is invalid
     */
    public function __construct(
        public readonly string $amount,
        public readonly Currency $currency,
    ) {
        $this->validate();
    }

    /**
     * Creates a Money instance from JSON-compatible data.
     *
     * @param mixed $data Array with 'amount' and 'currencyCode' keys
     *
     * @throws InvalidArgumentException When data is invalid
     */
    public static function fromData(mixed $data): static
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Money data must be an array with amount and currencyCode');
        }

        if (!isset($data['amount'])) {
            throw new InvalidArgumentException('Missing required field: amount');
        }

        if (!isset($data['currencyCode'])) {
            throw new InvalidArgumentException('Missing required field: currencyCode');
        }

        $currency = Currency::tryFrom($data['currencyCode']);
        if ($currency === null) {
            throw new InvalidArgumentException("Invalid currency code: {$data['currencyCode']}");
        }

        return new self(
            amount: (string) $data['amount'],
            currency: $currency,
        );
    }

    /**
     * Converts the Money instance to JSON-compatible data.
     *
     * @return array{amount: string, currencyCode: string}
     */
    public function toData(): mixed
    {
        return [
            'amount' => $this->amount,
            'currencyCode' => $this->currency->value,
        ];
    }

    /**
     * Checks if this Money instance equals another.
     *
     * Two Money instances are equal if they have the same amount and currency.
     *
     * @param Money $other The Money instance to compare with
     */
    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount
            && $this->currency === $other->currency;
    }

    /**
     * Checks if this Money instance has the same currency as another.
     *
     * @param Money $other The Money instance to compare with
     */
    public function hasSameCurrency(Money $other): bool
    {
        return $this->currency === $other->currency;
    }

    /**
     * Returns a new Money instance with the amount negated.
     *
     * @throws InvalidArgumentException When the negated amount is invalid
     */
    public function negate(): self
    {
        $negatedAmount = $this->amount;

        if (str_starts_with($negatedAmount, '-')) {
            $negatedAmount = substr($negatedAmount, 1);
        } else {
            $negatedAmount = '-' . $negatedAmount;
        }

        return new self($negatedAmount, $this->currency);
    }

    /**
     * Checks if the amount is positive (> 0).
     */
    public function isPositive(): bool
    {
        return bccomp($this->amount, '0', 4) > 0;
    }

    /**
     * Checks if the amount is negative (< 0).
     */
    public function isNegative(): bool
    {
        return bccomp($this->amount, '0', 4) < 0;
    }

    /**
     * Checks if the amount is zero.
     */
    public function isZero(): bool
    {
        return bccomp($this->amount, '0', 4) === 0;
    }

    /**
     * Validates the amount format.
     *
     * @throws InvalidArgumentException When amount format is invalid
     */
    private function validate(): void
    {
        // Check if amount matches the required pattern:
        // - Optional minus sign
        // - At most 9 integer digits
        // - Optional decimal point followed by at most 4 fractional digits
        if (!preg_match('/^-?\d{1,9}(\.\d{1,4})?$/', $this->amount)) {
            throw new InvalidArgumentException(
                "Invalid amount format: '{$this->amount}'. " .
                'Amount must have at most 9 integer digits and at most 4 fractional digits.'
            );
        }

        // Additional validation: ensure it's a valid numeric string
        if (!is_numeric($this->amount)) {
            throw new InvalidArgumentException("Amount must be numeric: '{$this->amount}'");
        }
    }
}
