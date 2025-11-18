<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\CardScheme;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * Card data transfer object.
 *
 * Represents payment card details for transaction requests and responses.
 * For requests: includes sensitive data (number, securityCode)
 * For responses: includes masked/token data (last4, bin, scheme, etc.)
 *
 * Note: Uses custom implementation instead of SerializesData trait due to
 * special validation requirements in fromArray() method (e.g., enum validation with error messages).
 */
class Card implements DataTransferObject
{
    /**
     * Get property type definitions for this DTO.
     *
     * @return array<string, array<string>>
     */
    public static function getPropertyTypes(): array
    {
        return [
            'string' => ['number', 'securityCode', 'holderName', 'last4', 'bin', 'fingerprint'],
            'int' => ['expirationMonth', 'expirationYear'],
            'enum' => ['scheme'],
        ];
    }
    /**
     * @param string|null $number Card number (PAN) - writeOnly, used in requests
     * @param string|null $securityCode Security code (CVV/CVC) - writeOnly, used in requests
     * @param int|null $expirationMonth Card expiration month (1-12)
     * @param int|null $expirationYear Card expiration year (2000-2099)
     * @param string|null $holderName Cardholder's name as it appears on the card
     * @param string|null $last4 Last 4 digits - readOnly, from responses
     * @param string|null $bin Bank identification number (first 6 digits) - readOnly
     * @param CardScheme|null $scheme Card scheme/network - readOnly
     * @param string|null $fingerprint Card fingerprint - readOnly
     *
     * @throws InvalidArgumentException When validation fails
     */
    public function __construct(
        public readonly ?string $number = null,
        public readonly ?string $securityCode = null,
        public readonly ?int $expirationMonth = null,
        public readonly ?int $expirationYear = null,
        public readonly ?string $holderName = null,
        public readonly ?string $last4 = null,
        public readonly ?string $bin = null,
        public readonly ?CardScheme $scheme = null,
        public readonly ?string $fingerprint = null,
    ) {
        $this->validate();
    }

    /**
     * Creates a Card instance from JSON-compatible data.
     *
     * @param mixed $data Array with card data
     *
     * @throws InvalidArgumentException When data is invalid
     */
    public static function fromData(mixed $data): static
    {
        // Parse scheme if present
        $scheme = null;
        if (isset($data['scheme'])) {
            $scheme = CardScheme::tryFrom($data['scheme']);
            if ($scheme === null) {
                throw new InvalidArgumentException("Invalid card scheme: {$data['scheme']}");
            }
        }

        return new self(
            number: isset($data['number']) ? (string) $data['number'] : null,
            securityCode: isset($data['securityCode']) ? (string) $data['securityCode'] : null,
            expirationMonth: isset($data['expirationMonth']) ? (int) $data['expirationMonth'] : null,
            expirationYear: isset($data['expirationYear']) ? (int) $data['expirationYear'] : null,
            holderName: isset($data['holderName']) ? (string) $data['holderName'] : null,
            last4: isset($data['last4']) ? (string) $data['last4'] : null,
            bin: isset($data['bin']) ? (string) $data['bin'] : null,
            scheme: $scheme,
            fingerprint: isset($data['fingerprint']) ? (string) $data['fingerprint'] : null,
        );
    }

    /**
     * Converts the Card to JSON-compatible data.
     *
     * Only includes non-null values for cleaner JSON serialization.
     *
     * @return mixed
     */
    public function toData(): mixed
    {
        $data = [];

        if ($this->number !== null) {
            $data['number'] = $this->number;
        }

        if ($this->securityCode !== null) {
            $data['securityCode'] = $this->securityCode;
        }

        if ($this->expirationMonth !== null) {
            $data['expirationMonth'] = $this->expirationMonth;
        }

        if ($this->expirationYear !== null) {
            $data['expirationYear'] = $this->expirationYear;
        }

        if ($this->holderName !== null) {
            $data['holderName'] = $this->holderName;
        }

        if ($this->last4 !== null) {
            $data['last4'] = $this->last4;
        }

        if ($this->bin !== null) {
            $data['bin'] = $this->bin;
        }

        if ($this->scheme !== null) {
            $data['scheme'] = $this->scheme->value;
        }

        if ($this->fingerprint !== null) {
            $data['fingerprint'] = $this->fingerprint;
        }

        return $data;
    }

    /**
     * Returns a shallow array of all non-null properties.
     *
     * @return array<string, mixed>
     */
    public function toObjectArray(): array
    {
        $data = [];

        $properties = ['number', 'securityCode', 'expirationMonth', 'expirationYear',
                       'holderName', 'last4', 'bin', 'scheme', 'fingerprint'];

        foreach ($properties as $prop) {
            if ($this->$prop !== null) {
                $data[$prop] = $this->$prop;
            }
        }

        return $data;
    }

    /**
     * Validates card data.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // Validate card number format (if present)
        if ($this->number !== null) {
            $this->validateCardNumber($this->number);
        }

        // Validate security code format (if present)
        if ($this->securityCode !== null) {
            $this->validateSecurityCode($this->securityCode);
        }

        // Validate expiration month (if present)
        if ($this->expirationMonth !== null) {
            if ($this->expirationMonth < 1 || $this->expirationMonth > 12) {
                throw new InvalidArgumentException(
                    "Expiration month must be between 1 and 12, got: {$this->expirationMonth}"
                );
            }
        }

        // Validate expiration year (if present)
        if ($this->expirationYear !== null) {
            if ($this->expirationYear < 2000 || $this->expirationYear > 2099) {
                throw new InvalidArgumentException(
                    "Expiration year must be between 2000 and 2099, got: {$this->expirationYear}"
                );
            }
        }
    }

    /**
     * Validates card number format.
     *
     * Pattern allows digits with optional non-digit separators (spaces, dashes)
     * Must contain 13-19 digits total
     *
     * @throws InvalidArgumentException When card number is invalid
     */
    private function validateCardNumber(string $number): void
    {
        // Remove all non-digits to count digits
        $digitsOnly = preg_replace('/\D/', '', $number);

        if ($digitsOnly === null || strlen($digitsOnly) < 13 || strlen($digitsOnly) > 19) {
            throw new InvalidArgumentException(
                'Card number must contain 13-19 digits'
            );
        }

        // Validate against EPG pattern: \D*(?:\d\D*){13,19}
        // This allows optional non-digit characters between digits
        if (!preg_match('/^\D*(?:\d\D*){13,19}$/', $number)) {
            throw new InvalidArgumentException(
                'Card number format is invalid'
            );
        }
    }

    /**
     * Validates security code format.
     *
     * Must be 3 or 4 digits
     *
     * @throws InvalidArgumentException When security code is invalid
     */
    private function validateSecurityCode(string $code): void
    {
        if (!preg_match('/^\d{3,4}$/', $code)) {
            throw new InvalidArgumentException(
                'Security code must be 3 or 4 digits'
            );
        }
    }
}