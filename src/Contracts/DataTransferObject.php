<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Contracts;

/**
 * Interface for Data Transfer Objects.
 *
 * Extends ValueObject with additional capabilities provided by the SerializesData trait.
 * Type information is derived from constructor parameters via reflection.
 */
interface DataTransferObject extends ValueObject
{
    /**
     * Returns a shallow array of all non-null properties.
     *
     * Unlike toData(), this does not recurse into nested objects.
     *
     * @return array<string, mixed>
     */
    public function toObjectArray(): array;
}
