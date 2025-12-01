<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Attributes\ArrayOf;
use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\ValueObjects\CustomFields;
use Money\Money;

/**
 * Order data transfer object.
 *
 * An order details what a shopper is paying for.
 * All properties are optional as different fields are used in different contexts.
 * Validation of required fields should occur at the message level (CreateOrderRequest, etc.).
 *
 * Properties marked [Response] are typically only present in API responses.
 * Properties marked [Request] are typically sent in API requests.
 * Properties without markers may appear in both contexts.
 */
class Order implements DataTransferObject
{
    use SerializesData;

    public function __construct(
        // Response-only fields
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $modifiedAt = null,
        public readonly ?string $merchant = null,

        // Request/Response fields
        public readonly ?Money $total = null,
        public readonly ?string $description = null,
        /** @var array<OrderItem>|null */
        #[ArrayOf(OrderItem::class)]
        public readonly ?array $items = null,
        public readonly ?Contact $shipTo = null,
        public readonly ?string $shopperEmailAddress = null,
        public readonly ?string $shopperReference = null,
        public readonly ?string $orderReference = null,
        public readonly ?string $customReference = null,
        public readonly ?CustomFields $customFields = null,
    ) {
        $this->validate();
    }

    /**
     * Validates order data.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // Validate description length
        if ($this->description !== null && strlen($this->description) > 255) {
            throw new InvalidArgumentException('Description must not exceed 255 characters');
        }

        // Validate shopperEmailAddress length
        if ($this->shopperEmailAddress !== null && strlen($this->shopperEmailAddress) > 254) {
            throw new InvalidArgumentException('Shopper email address must not exceed 254 characters');
        }

        // Validate shopperReference length
        if ($this->shopperReference !== null && strlen($this->shopperReference) > 255) {
            throw new InvalidArgumentException('Shopper reference must not exceed 255 characters');
        }

        // Validate orderReference length
        if ($this->orderReference !== null && strlen($this->orderReference) > 255) {
            throw new InvalidArgumentException('Order reference must not exceed 255 characters');
        }

        // Validate customReference length
        if ($this->customReference !== null && strlen($this->customReference) > 255) {
            throw new InvalidArgumentException('Custom reference must not exceed 255 characters');
        }

        // Validate items count (max 64)
        if ($this->items !== null && count($this->items) > 64) {
            throw new InvalidArgumentException('Items must not exceed 64 entries');
        }

        // customFields validation is handled by CustomFields value object
    }
}
