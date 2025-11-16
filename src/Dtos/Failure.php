<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * Failure data transfer object.
 *
 * Represents a failure with code, target field, and description.
 * All properties are read-only and typically only present in API responses.
 */
class Failure implements DataTransferObject
{
    use SerializesData;

    /**
     * Get property type definitions for this DTO.
     *
     * @return array<string, array<string>>
     */
    public static function getPropertyTypes(): array
    {
        return [
            'string' => ['code', 'description', 'field'],
        ];
    }

    /**
     * @param string|null $code Failure code (e.g., "unauthorized")
     * @param string|null $description Description of the failure (intended for developers)
     * @param string|null $field Field, if failure is linked to a specific field
     */
    public function __construct(
        public readonly ?string $code = null,
        public readonly ?string $description = null,
        public readonly ?string $field = null,
    ) {
    }
}
