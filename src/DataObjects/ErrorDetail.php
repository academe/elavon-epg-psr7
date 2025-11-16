<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\DataObjects;

/**
 * Error Detail.
 *
 * Represents a single error/failure detail from an API error response.
 */
class ErrorDetail
{
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

    /**
     * Creates an ErrorDetail from an array.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            code: (string) ($data['code'] ?? ''),
            description: (string) ($data['description'] ?? ''),
            field: isset($data['field']) && $data['field'] !== null ? (string) $data['field'] : null,
        );
    }

    /**
     * Converts the ErrorDetail to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'code' => $this->code,
            'description' => $this->description,
            'field' => $this->field,
        ], fn($value) => $value !== null);
    }
}
