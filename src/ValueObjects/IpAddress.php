<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\ValueObjects;

use Academe\Elavon\Epg\Psr7\Contracts\ValueObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * IP Address value object.
 *
 * Represents a validated IPv4 or IPv6 address.
 * Serializes to a simple string value.
 */
class IpAddress implements ValueObject
{
    /**
     * @param string $address The IP address
     */
    public function __construct(
        public readonly string $address,
    ) {
        $this->validate();
    }

    /**
     * Creates an IpAddress instance from JSON-compatible data.
     *
     * @param mixed $data String IP address
     *
     * @throws InvalidArgumentException When data is invalid
     */
    public static function fromData(mixed $data): static
    {
        if (!is_string($data)) {
            throw new InvalidArgumentException('IP address must be a string');
        }

        return new self(address: $data);
    }

    /**
     * Converts the IpAddress to JSON-compatible data.
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
     * Validates the IP address format.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        if (empty($this->address)) {
            throw new InvalidArgumentException('IP address cannot be empty');
        }

        // Validate IP address (both IPv4 and IPv6)
        if (filter_var($this->address, FILTER_VALIDATE_IP) === false) {
            throw new InvalidArgumentException(
                "Invalid IP address format: '{$this->address}'"
            );
        }
    }

    /**
     * Returns the IP address as a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->address;
    }

    /**
     * Checks if this is an IPv4 address.
     *
     * @return bool
     */
    public function isIPv4(): bool
    {
        return filter_var($this->address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    /**
     * Checks if this is an IPv6 address.
     *
     * @return bool
     */
    public function isIPv6(): bool
    {
        return filter_var($this->address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }
}
