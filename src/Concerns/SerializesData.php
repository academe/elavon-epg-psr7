<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Concerns;

use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;

/**
 * Trait for data-driven serialization and deserialization of DTOs.
 *
 * Provides reusable implementations of fromArray(), toArray(), and toObjectArray()
 * based on property type definitions from getPropertyTypes().
 */
trait SerializesData
{
    /**
     * Creates an instance from an array representation.
     *
     * @param array<string, mixed> $data Array with DTO data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        /** @var DataTransferObject $class */
        $class = static::class;
        $propertyTypes = $class::getPropertyTypes();

        // Build constructor arguments dynamically using property type definitions
        $args = [];

        // Money, Object, and Array properties - pass raw data, constructor handles conversion
        $objectProperties = array_merge(
            $propertyTypes['money'] ?? [],
            $propertyTypes['object'] ?? [],
            $propertyTypes['array'] ?? []
        );

        foreach ($objectProperties as $prop) {
            $args[$prop] = $data[$prop] ?? null;
        }

        // Enum properties - pass raw data, constructor handles conversion
        foreach ($propertyTypes['enum'] ?? [] as $prop) {
            $args[$prop] = $data[$prop] ?? null;
        }

        // String properties - cast to string if present
        foreach ($propertyTypes['string'] ?? [] as $prop) {
            $args[$prop] = isset($data[$prop]) ? (string) $data[$prop] : null;
        }

        // Boolean properties - cast to bool if present
        foreach ($propertyTypes['boolean'] ?? [] as $prop) {
            $args[$prop] = isset($data[$prop]) ? (bool) $data[$prop] : null;
        }

        // Integer properties - cast to int if present
        foreach ($propertyTypes['int'] ?? [] as $prop) {
            $args[$prop] = isset($data[$prop]) ? (int) $data[$prop] : null;
        }

        // Unpack arguments array as named parameters
        return new static(...$args);
    }

    /**
     * Returns a shallow array of all non-null properties.
     *
     * Unlike toArray(), this does not recurse into nested objects - it returns
     * the actual object instances, arrays, enums, etc. as-is.
     *
     * @return array<string, mixed>
     */
    public function toObjectArray(): array
    {
        /** @var DataTransferObject $class */
        $class = static::class;
        $propertyTypes = $class::getPropertyTypes();

        $data = [];

        // Build complete property list from type definitions
        $allProperties = array_merge(
            $propertyTypes['money'] ?? [],
            $propertyTypes['object'] ?? [],
            $propertyTypes['array'] ?? [],
            $propertyTypes['enum'] ?? [],
            $propertyTypes['string'] ?? [],
            $propertyTypes['boolean'] ?? [],
            $propertyTypes['int'] ?? []
        );

        // Collect all non-null properties
        foreach ($allProperties as $property) {
            if ($this->$property !== null) {
                $data[$property] = $this->$property;
            }
        }

        return $data;
    }

    /**
     * Converts the DTO to an array representation.
     *
     * Recursively converts all nested objects to arrays for JSON serialization.
     * Only includes non-null values for cleaner JSON output.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        /** @var DataTransferObject $class */
        $class = static::class;
        $propertyTypes = $class::getPropertyTypes();

        $data = [];

        // Convert all DTO objects (Money, Card, Contact, etc.) to arrays
        $objectProperties = array_merge(
            $propertyTypes['money'] ?? [],
            $propertyTypes['object'] ?? []
        );

        foreach ($objectProperties as $prop) {
            if ($this->$prop !== null) {
                $data[$prop] = $this->$prop->toArray();
            }
        }

        // Handle array properties - can contain objects with toArray() or primitives
        foreach ($propertyTypes['array'] ?? [] as $prop) {
            if ($this->$prop !== null) {
                $data[$prop] = array_map(
                    fn($item) => is_object($item) && method_exists($item, 'toArray')
                        ? $item->toArray()
                        : $item,
                    $this->$prop
                );
            }
        }

        // Convert enum properties to their string values
        foreach ($propertyTypes['enum'] ?? [] as $prop) {
            if ($this->$prop !== null) {
                $data[$prop] = $this->$prop->value;
            }
        }

        // Add scalar properties (strings, booleans, and integers)
        $scalarProperties = array_merge(
            $propertyTypes['string'] ?? [],
            $propertyTypes['boolean'] ?? [],
            $propertyTypes['int'] ?? []
        );

        foreach ($scalarProperties as $prop) {
            if ($this->$prop !== null) {
                $data[$prop] = $this->$prop;
            }
        }

        return $data;
    }
}
