<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;

/**
 * Error Detail.
 *
 * Represents a single error/failure detail from an API error response.
 */
class ErrorDetail implements DataTransferObject
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
     * @param string $code Error code (e.g., "unauthorized", "validation_error")
     * @param string $description Human-readable error description
     * @param string|null $field Field name that caused the error (null for general errors)
     */
    public function __construct(
        public readonly string $code,
        public readonly string $description,
        public readonly ?string $field = null,
    ) {
    }

}
