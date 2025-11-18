<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\ValueObjects;

use Academe\Elavon\Epg\Psr7\Contracts\ValueObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * Email Address value object.
 *
 * Represents a validated email address.
 * Serializes to a simple string value.
 */
class EmailAddress implements ValueObject
{
    /**
     * @param string $address The email address
     */
    public function __construct(
        public readonly string $address,
    ) {
        $this->validate();
    }

    /**
     * Creates an EmailAddress instance from JSON-compatible data.
     *
     * @param mixed $data String email address
     *
     * @throws InvalidArgumentException When data is invalid
     */
    public static function fromData(mixed $data): static
    {
        if (!is_string($data)) {
            throw new InvalidArgumentException('Email address must be a string');
        }

        return new self(address: $data);
    }

    /**
     * Converts the EmailAddress to JSON-compatible data.
     *
     * Returns a simple string representation.
     *
     * @return string
     */
    public function toData(): mixed
    {
        return $this->address;
    }

    /**
     * Validates the email address format.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        if (empty($this->address)) {
            throw new InvalidArgumentException('Email address cannot be empty');
        }

        // Length validation (RFC 5321) - check before format validation
        if (strlen($this->address) > 254) {
            throw new InvalidArgumentException(
                'Email address cannot exceed 254 characters'
            );
        }

        // Basic email validation using filter_var
        if (filter_var($this->address, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException(
                "Invalid email address format: '{$this->address}'"
            );
        }
    }

    /**
     * Returns the email address as a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->address;
    }
}
