<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Attributes;

use Attribute;

/**
 * Specifies the element type for array properties in DTOs.
 *
 * This attribute enables automatic conversion of array elements during
 * fromData() deserialization. It supports three types of arrays:
 *
 * 1. DTO arrays: Elements are converted using ClassName::fromData()
 * 2. Enum arrays: String values are converted to enum instances
 * 3. Scalar arrays: Values are cast to the specified type (string, int, bool)
 *
 * Usage:
 *
 * ```php
 * public function __construct(
 *     #[ArrayOf(OrderItem::class)]
 *     public readonly ?array $items = null,
 *
 *     #[ArrayOf(CardBrand::class)]
 *     public readonly ?array $supportedCardBrands = null,
 *
 *     #[ArrayOf('string')]
 *     public readonly ?array $status = null,
 * ) {}
 * ```
 */
#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class ArrayOf
{
    /**
     * @param class-string|'string'|'int'|'bool' $type The element type
     */
    public function __construct(
        public readonly string $type,
    ) {
    }

    /**
     * Check if the type is a class (DTO or enum).
     */
    public function isClass(): bool
    {
        return class_exists($this->type) || enum_exists($this->type);
    }

    /**
     * Check if the type is an enum.
     */
    public function isEnum(): bool
    {
        return enum_exists($this->type);
    }

    /**
     * Check if the type is a DTO (has fromData method).
     */
    public function isDto(): bool
    {
        return class_exists($this->type) && method_exists($this->type, 'fromData');
    }

    /**
     * Check if the type is a scalar (string, int, bool).
     */
    public function isScalar(): bool
    {
        return in_array($this->type, ['string', 'int', 'bool'], true);
    }

    /**
     * Convert a single value to the target type.
     *
     * @param mixed $value The value to convert
     * @return mixed The converted value
     */
    public function convertValue(mixed $value): mixed
    {
        // Already the correct type
        if (is_object($value) && $this->isClass() && $value instanceof ($this->type)) {
            return $value;
        }

        // Convert to enum
        if ($this->isEnum()) {
            /** @var class-string<\BackedEnum> $enumClass */
            $enumClass = $this->type;
            return $enumClass::from($value);
        }

        // Convert to DTO
        if ($this->isDto()) {
            /** @var class-string $dtoClass */
            $dtoClass = $this->type;
            return $dtoClass::fromData($value);
        }

        // Convert to scalar
        return match ($this->type) {
            'string' => (string) $value,
            'int' => (int) $value,
            'bool' => (bool) $value,
            default => $value,
        };
    }

    /**
     * Convert an array of values to the target type.
     *
     * @param array|null $values The values to convert
     * @return array|null The converted values
     */
    public function convertArray(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        return array_map(fn($value) => $this->convertValue($value), $values);
    }
}
