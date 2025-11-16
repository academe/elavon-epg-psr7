<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\DataObjects;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * Failure data transfer object.
 *
 * Represents a failure with code, target field, and description.
 * All properties are read-only and typically only present in API responses.
 */
class Failure
{
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

    /**
     * Creates a Failure instance from an array representation.
     *
     * @param array<string, mixed> $data Array with failure data
     *
     * @throws InvalidArgumentException When data is invalid
     */
    public static function fromArray(array $data): self
    {
        return new self(
            code: isset($data['code']) ? (string) $data['code'] : null,
            description: isset($data['description']) ? (string) $data['description'] : null,
            field: isset($data['field']) ? (string) $data['field'] : null,
        );
    }

    /**
     * Converts the Failure to an array representation.
     *
     * Only includes non-null values for cleaner JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->code !== null) {
            $data['code'] = $this->code;
        }

        if ($this->description !== null) {
            $data['description'] = $this->description;
        }

        if ($this->field !== null) {
            $data['field'] = $this->field;
        }

        return $data;
    }
}
