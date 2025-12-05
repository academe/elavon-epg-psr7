<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\ValueObjects;

use Academe\Elavon\Epg\Psr7\Contracts\ValueObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use ArrayAccess;
use Countable;
use IteratorAggregate;
use Traversable;
use ArrayIterator;

/**
 * Custom fields value object.
 *
 * Represents a dictionary of custom string key-value pairs with validation.
 * Field names must not exceed 64 characters, values must not exceed 1024 characters.
 *
 * Implements ArrayAccess for $customFields['key'] syntax.
 * Implements Countable for count($customFields) syntax.
 * Implements IteratorAggregate for foreach($customFields as $key => $value) syntax.
 *
 * @implements ArrayAccess<string, string>
 * @implements IteratorAggregate<string, string>
 */
class CustomFields implements ValueObject, ArrayAccess, Countable, IteratorAggregate
{
    private const MAX_KEY_LENGTH = 64;
    private const MAX_VALUE_LENGTH = 1024;

    /**
     * @param array<string, string> $fields The custom fields dictionary
     */
    public function __construct(
        private readonly array $fields = [],
    ) {
        $this->validate();
    }

    /**
     * Creates a CustomFields instance from JSON-compatible data.
     *
     * @param mixed $data Array of string key-value pairs
     * @throws InvalidArgumentException When data is invalid
     */
    public static function fromData(mixed $data): static
    {
        if ($data instanceof self) {
            return $data;
        }

        if (!is_array($data)) {
            throw new InvalidArgumentException('Custom fields must be an array');
        }

        return new self(fields: $data);
    }

    /**
     * Converts the CustomFields to JSON-compatible data.
     *
     * Returns the raw array for serialization.
     *
     * @return array<string, string>
     */
    public function toData(): array
    {
        return $this->fields;
    }

    /**
     * Returns the underlying array.
     *
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->fields;
    }

    /**
     * Gets a field value by key.
     *
     * @param string $key The field name
     * @param string|null $default Default value if key doesn't exist
     * @return string|null
     */
    public function get(string $key, ?string $default = null): ?string
    {
        return $this->fields[$key] ?? $default;
    }

    /**
     * Checks if a field exists.
     *
     * @param string $key The field name
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->fields);
    }

    /**
     * Returns a new instance with an additional field.
     *
     * @param string $key The field name
     * @param string $value The field value
     * @return static
     */
    public function with(string $key, string $value): static
    {
        return new self(array_merge($this->fields, [$key => $value]));
    }

    /**
     * Returns a new instance without the specified field.
     *
     * @param string $key The field name to remove
     * @return static
     */
    public function without(string $key): static
    {
        $fields = $this->fields;
        unset($fields[$key]);
        return new self($fields);
    }

    /**
     * Validates the custom fields.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        foreach ($this->fields as $key => $value) {
            if (!is_string($key)) {
                throw new InvalidArgumentException('Custom field names must be strings');
            }

            if (strlen($key) > self::MAX_KEY_LENGTH) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Custom field name must not exceed %d characters, got %d for key "%s"',
                        self::MAX_KEY_LENGTH,
                        strlen($key),
                        substr($key, 0, 20) . '...'
                    )
                );
            }

            if (!is_string($value)) {
                throw new InvalidArgumentException(
                    sprintf('Custom field value for "%s" must be a string', $key)
                );
            }

            if (strlen($value) > self::MAX_VALUE_LENGTH) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Custom field value for "%s" must not exceed %d characters, got %d',
                        $key,
                        self::MAX_VALUE_LENGTH,
                        strlen($value)
                    )
                );
            }
        }
    }

    // ArrayAccess implementation

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->fields[$offset]);
    }

    public function offsetGet(mixed $offset): ?string
    {
        return $this->fields[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \LogicException('CustomFields is immutable');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new \LogicException('CustomFields is immutable');
    }

    // Countable implementation

    public function count(): int
    {
        return count($this->fields);
    }

    // IteratorAggregate implementation

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->fields);
    }

    /**
     * Checks if the custom fields collection is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->fields);
    }
}
