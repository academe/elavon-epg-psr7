<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\ValueObjects\CustomFields;
use DateTimeImmutable;
use Money\Money;

/**
 * Plan data transfer object.
 *
 * A plan provides a template for paying for something over time with multiple payments.
 * All properties are optional as different fields are used in different contexts.
 * Validation of required fields should occur at the message level (CreatePlanRequest, etc.).
 *
 * Properties marked [Response] are typically only present in API responses.
 * Properties marked [Request] are typically sent in API requests.
 * Properties without markers may appear in both contexts.
 */
class Plan implements DataTransferObject
{
    use SerializesData;

    public function __construct(
        // Response-only fields
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $modifiedAt = null,
        public readonly ?DateTimeImmutable $deletedAt = null,
        public readonly ?string $merchant = null,

        // Request/Response fields
        public readonly ?string $planList = null,
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly ?BillingInterval $billingInterval = null,
        public readonly ?Money $total = null,
        public readonly ?Money $salesTax = null,
        public readonly ?int $billCount = null,
        public readonly ?Money $initialTotal = null,
        public readonly ?Money $initialSalesTax = null,
        public readonly ?int $initialTotalBillCount = null,
        public readonly ?ShopperStatement $shopperStatement = null,
        public readonly ?bool $isSubscribable = null,
        public readonly ?string $customReference = null,
        public readonly ?CustomFields $customFields = null,
    ) {
        $this->validate();
    }

    /**
     * Validates plan data.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // Validate name length
        if ($this->name !== null && strlen($this->name) > 255) {
            throw new InvalidArgumentException('Name must not exceed 255 characters');
        }

        // Validate description length
        if ($this->description !== null && strlen($this->description) > 255) {
            throw new InvalidArgumentException('Description must not exceed 255 characters');
        }

        // Validate billCount (minimum 1)
        if ($this->billCount !== null && $this->billCount < 1) {
            throw new InvalidArgumentException('Bill count must be at least 1');
        }

        // Validate initialTotalBillCount (minimum 0)
        if ($this->initialTotalBillCount !== null && $this->initialTotalBillCount < 0) {
            throw new InvalidArgumentException('Initial total bill count must be at least 0');
        }

        // Validate customReference length
        if ($this->customReference !== null && strlen($this->customReference) > 255) {
            throw new InvalidArgumentException('Custom reference must not exceed 255 characters');
        }

        // customFields validation is handled by CustomFields value object
    }
}
