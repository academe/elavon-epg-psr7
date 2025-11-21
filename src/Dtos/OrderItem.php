<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\OrderItemType;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\ValueObjects\Money;

/**
 * OrderItem data transfer object.
 *
 * Represents a line item in an order.
 * All properties are read-only.
 */
class OrderItem implements DataTransferObject
{
    use SerializesData;

    // Normalized properties (objects)
    public readonly ?Money $total;
    public readonly ?Money $unitPrice;

    // Normalized enum
    public readonly ?OrderItemType $type;

    /**
     * Get property type definitions for this DTO.
     *
     * @return array<string, array<string>>
     */
    public static function getPropertyTypes(): array
    {
        return [
            'object' => ['total', 'unitPrice'],
            'string' => ['description', 'customReference'],
            'int' => ['quantity'],
            'enum' => ['type'],
        ];
    }

    /**
     * @param Money|array{amount: string, currencyCode: string}|null $total Total for this item, accounting for quantity (required)
     * @param string|null $description Description of the item (min 1, max 255 chars)
     * @param Money|array{amount: string, currencyCode: string}|null $unitPrice Cost of an individual unit
     * @param int|null $quantity The number of units being purchased (min 1, default 1)
     * @param string|null $customReference Optional reference provided by the merchant (max 255 chars)
     * @param OrderItemType|string|null $type Item type (optional, defaults to 'unknown')
     *
     * @throws InvalidArgumentException When validation fails
     */
    public function __construct(
        Money|array|null $total = null,
        public readonly ?string $description = null,
        Money|array|null $unitPrice = null,
        public readonly ?int $quantity = null,
        public readonly ?string $customReference = null,
        OrderItemType|string|null $type = null,
    ) {
        // Normalize Money objects
        $this->total = match (true) {
            $total instanceof Money => $total,
            is_array($total) => Money::fromData($total),
            default => null,
        };

        $this->unitPrice = match (true) {
            $unitPrice instanceof Money => $unitPrice,
            is_array($unitPrice) => Money::fromData($unitPrice),
            default => null,
        };

        // Normalize OrderItemType enum
        $this->type = $this->normalizeEnum($type, OrderItemType::class, 'type');

        $this->validate();
    }

    /**
     * Normalizes an enum value from either enum object or string.
     *
     * @template T of \BackedEnum
     * @param T|string|null $value
     * @param class-string<T> $enumClass
     * @param string $fieldName
     * @return T|null
     * @throws InvalidArgumentException When string value is invalid
     */
    private function normalizeEnum(mixed $value, string $enumClass, string $fieldName): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof $enumClass) {
            return $value;
        }

        if (is_string($value)) {
            $enum = $enumClass::tryFrom($value);
            if ($enum === null) {
                throw new InvalidArgumentException("Invalid {$fieldName}: {$value}");
            }
            return $enum;
        }

        throw new InvalidArgumentException(
            "Field {$fieldName} must be a {$enumClass} enum or string, " . get_debug_type($value) . " given"
        );
    }

    /**
     * Validates order item data.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // Validate description length
        if ($this->description !== null) {
            $length = strlen($this->description);
            if ($length < 1 || $length > 255) {
                throw new InvalidArgumentException('Description must be between 1 and 255 characters');
            }
        }

        // Validate customReference length
        if ($this->customReference !== null && strlen($this->customReference) > 255) {
            throw new InvalidArgumentException('Custom reference must not exceed 255 characters');
        }

        // Validate quantity (must be >= 1 if present)
        if ($this->quantity !== null && $this->quantity < 1) {
            throw new InvalidArgumentException('Quantity must be at least 1');
        }
    }
}
