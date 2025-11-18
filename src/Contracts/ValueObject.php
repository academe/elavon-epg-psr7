<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Contracts;

/**
 * Interface for Value Objects and Data Transfer Objects.
 *
 * Defines the base contract for objects that can be serialized to/from JSON-compatible data.
 * This simpler interface can be used by value objects that don't need the full
 * property type system provided by DataTransferObject.
 *
 * The data can be:
 * - Arrays for complex objects (e.g., Money: ["amount" => "99.99", "currencyCode" => "USD"])
 * - Scalars for simple wrappers (e.g., EmailAddress: "user@example.com")
 * - Any JSON-serializable type
 */
interface ValueObject
{
    /**
     * Creates an instance from JSON-compatible data.
     *
     * @param mixed $data Can be an array, string, int, bool, or any JSON-serializable type
     * @return static
     */
    public static function fromData(mixed $data): static;

    /**
     * Converts to JSON-compatible data.
     *
     * Returns the simplest representation suitable for JSON serialization.
     * Can return an array, string, int, bool, or any JSON-serializable type.
     *
     * @return mixed
     */
    public function toData(): mixed;
}
