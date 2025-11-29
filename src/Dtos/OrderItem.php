<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\OrderItemType;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Money\Money;

/**
 * OrderItem data transfer object.
 *
 * Represents a line item in an order.
 * All properties are read-only.
 */
class OrderItem implements DataTransferObject
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
            'money' => ['total', 'unitPrice'],
            'string' => ['description', 'customReference'],
            'int' => ['quantity'],
            'enum' => ['type'],
        ];
    }

    /**
     * @param Money|null $total Total for this item, accounting for quantity (required)
     * @param string|null $description Description of the item (min 1, max 255 chars)
     * @param Money|null $unitPrice Cost of an individual unit
     * @param int|null $quantity The number of units being purchased (min 1, default 1)
     * @param string|null $customReference Optional reference provided by the merchant (max 255 chars)
     * @param OrderItemType|string|null $type Item type (optional, defaults to 'unknown')
     *
     * @throws InvalidArgumentException When validation fails
     */
    public function __construct(
        public readonly ?Money $total = null,
        public readonly ?string $description = null,
        public readonly ?Money $unitPrice = null,
        public readonly ?int $quantity = null,
        public readonly ?string $customReference = null,
        public readonly ?OrderItemType $type = null,
    ) {
        $this->validate();
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
