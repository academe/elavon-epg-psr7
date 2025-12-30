<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;

/**
 * History Entry data transfer object.
 *
 * Represents a transaction history entry with arbitrary properties.
 * The OpenAPI spec defines this with additionalProperties: object,
 * so we use a flexible array structure.
 */
class HistoryEntry implements DataTransferObject
{
    /**
     * @param array<string, mixed> $data The history entry data
     */
    public function __construct(
        private readonly array $data = [],
    ) {
    }

    /**
     * Creates a HistoryEntry instance from JSON-compatible data.
     *
     * @param mixed $data Array with history entry data
     */
    public static function fromData(mixed $data): static
    {
        if ($data instanceof self) {
            return $data;
        }

        if (!is_array($data)) {
            return new self([]);
        }

        return new self($data);
    }

    /**
     * Converts the HistoryEntry to JSON-compatible data.
     *
     * @return array<string, mixed>
     */
    public function toData(): array
    {
        return $this->data;
    }

    /**
     * Returns a shallow array of all properties.
     *
     * @return array<string, mixed>
     */
    public function toObjectArray(): array
    {
        return $this->data;
    }

    /**
     * Gets a value from the history entry.
     *
     * @param string $key The key to retrieve
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Checks if a key exists in the history entry.
     *
     * @param string $key The key to check
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Returns all data as an array.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->data;
    }
}