<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Contracts;

/**
 * Interface for Value Objects and Data Transfer Objects.
 *
 * Defines the base contract for objects that can be serialized to/from arrays.
 * This simpler interface can be used by value objects that don't need the full
 * property type system provided by DataTransferObject.
 */
interface ValueObject
{
    /**
     * Creates an instance from an array representation.
     *
     * @param array<string, mixed> $data Array with DTO data
     * @return static
     */
    public static function fromArray(array $data): static;

    /**
     * Converts the DTO to an array representation.
     *
     * Recursively converts all nested objects to arrays for JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
