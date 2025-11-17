<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\ValueObjects;

use Academe\Elavon\Epg\Psr7\Contracts\ValueObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * URL value object.
 *
 * Represents a validated URL.
 * Serializes to a simple string value.
 */
class Url implements ValueObject
{
    /**
     * @param string $url The URL
     */
    public function __construct(
        public readonly string $url,
    ) {
        $this->validate();
    }

    /**
     * Creates a Url instance from JSON-compatible data.
     *
     * @param mixed $data String URL
     *
     * @throws InvalidArgumentException When data is invalid
     */
    public static function fromData(mixed $data): static
    {
        if (!is_string($data)) {
            throw new InvalidArgumentException('URL must be a string');
        }

        return new self(url: $data);
    }

    /**
     * Converts the Url to JSON-compatible data.
     *
     * Returns a simple string representation.
     *
     * @return string
     */
    public function toData(): mixed
    {
        return $this->url;
    }

    /**
     * Validates the URL format.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        if (empty($this->url)) {
            throw new InvalidArgumentException('URL cannot be empty');
        }

        // Validate URL using filter_var
        if (filter_var($this->url, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException(
                "Invalid URL format: '{$this->url}'"
            );
        }

        // Additional length validation (reasonable limit)
        if (strlen($this->url) > 2048) {
            throw new InvalidArgumentException(
                'URL cannot exceed 2048 characters'
            );
        }
    }

    /**
     * Returns the URL as a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->url;
    }
}
