<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Contracts;

/**
 * Interface for Data Transfer Objects.
 *
 * Extends ValueObject with additional metadata requirements for the SerializesData trait.
 * DTOs that use the trait must implement getPropertyTypes() to define their structure.
 */
interface DataTransferObject extends ValueObject
{
    /**
     * Get property type definitions for this DTO.
     *
     * Returns an array mapping property types to their property names.
     * This allows the serialization logic to handle each type appropriately.
     *
     * Supported types:
     * - 'money': Properties containing Money objects
     * - 'object': Properties containing other DTOs with toArray() methods
     * - 'array': Properties containing arrays (of objects with toArray() or primitives)
     * - 'enum': Properties containing enum values
     * - 'string': Properties containing string values
     * - 'boolean': Properties containing boolean values
     * - 'int': Properties containing integer values
     *
     * Example:
     * [
     *     'money' => ['total', 'totalRefunded'],
     *     'object' => ['card', 'shipTo'],
     *     'array' => ['failures', 'tags'],
     *     'enum' => ['state', 'type'],
     *     'string' => ['id', 'description'],
     *     'boolean' => ['isAuthorized', 'isVoided'],
     *     'int' => ['expirationMonth', 'expirationYear'],
     * ]
     *
     * @return array<string, array<string>>
     */
    public static function getPropertyTypes(): array;

    /**
     * Returns a shallow array of all non-null properties.
     *
     * Unlike toArray(), this does not recurse into nested objects.
     *
     * @return array<string, mixed>
     */
    public function toObjectArray(): array;
}
