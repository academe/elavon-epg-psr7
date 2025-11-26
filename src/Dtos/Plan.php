<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
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

    // Normalized properties (objects)
    public readonly ?BillingInterval $billingInterval;
    public readonly ?ShopperStatement $shopperStatement;

    /**
     * Get property type definitions for this DTO.
     *
     * @return array<string, array<string>>
     */
    public static function getPropertyTypes(): array
    {
        return [
            'money' => ['total', 'salesTax', 'initialTotal', 'initialSalesTax'],
            'object' => ['billingInterval', 'shopperStatement'],
            'array' => ['customFields'],
            'string' => [
                'href', 'id', 'createdAt', 'modifiedAt', 'deletedAt', 'merchant',
                'planList', 'name', 'description', 'customReference',
            ],
            'int' => ['billCount', 'initialTotalBillCount'],
            'bool' => ['isSubscribable'],
        ];
    }

    /**
     * @param string|null $href [Response] Plan Resource URL (self link)
     * @param string|null $id [Response] Plan Resource ID assigned by server
     * @param string|null $createdAt [Response] Creation timestamp
     * @param string|null $modifiedAt [Response] Modification timestamp
     * @param string|null $deletedAt [Response] Deletion timestamp
     * @param string|null $merchant [Response] Merchant Resource URL
     * @param string|null $planList PlanList Resource URL
     * @param string|null $name Name (max 255 chars, required for creation)
     * @param string|null $description Description (max 255 chars)
     * @param BillingInterval|array<string, mixed>|null $billingInterval Time period between bills (required for creation)
     * @param Money|null $total Total for each bill, except for any initial ones which might be different (required for creation)
     * @param Money|null $salesTax Sales Tax
     * @param int|null $billCount The total number of bills, if applicable (minimum 1)
     * @param Money|null $initialTotal Optional total override for initial bills to allow for trials, one-time initiation fees, etc.
     * @param Money|null $initialSalesTax Optional sales tax override for initial bills
     * @param int|null $initialTotalBillCount The number of initial bills where initialTotal will be applied (minimum 0)
     * @param ShopperStatement|array<string, mixed>|null $shopperStatement Dynamic overrides of what might appear on a shopper's statement
     * @param bool|null $isSubscribable Can shoppers be subscribed to this plan? Defaults to true
     * @param string|null $customReference Optional reference provided by the merchant (max 255 chars)
     * @param array<string, string>|null $customFields Custom fields, an object containing arbitrary string values (field names max 64 chars, values max 1024 chars)
     *
     * @throws InvalidArgumentException When validation fails
     */
    public function __construct(
        // Response-only fields
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $modifiedAt = null,
        public readonly ?string $deletedAt = null,
        public readonly ?string $merchant = null,

        // Request/Response fields
        public readonly ?string $planList = null,
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        BillingInterval|array|null $billingInterval = null,
        public readonly ?Money $total = null,
        public readonly ?Money $salesTax = null,
        public readonly ?int $billCount = null,
        public readonly ?Money $initialTotal = null,
        public readonly ?Money $initialSalesTax = null,
        public readonly ?int $initialTotalBillCount = null,
        ShopperStatement|array|null $shopperStatement = null,
        public readonly ?bool $isSubscribable = null,
        public readonly ?string $customReference = null,
        public readonly ?array $customFields = null,
    ) {
        // Normalize BillingInterval object
        $this->billingInterval = match (true) {
            $billingInterval instanceof BillingInterval => $billingInterval,
            is_array($billingInterval) => BillingInterval::fromData($billingInterval),
            default => null,
        };

        // Normalize ShopperStatement object
        $this->shopperStatement = match (true) {
            $shopperStatement instanceof ShopperStatement => $shopperStatement,
            is_array($shopperStatement) => ShopperStatement::fromData($shopperStatement),
            default => null,
        };

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

        // Validate customFields
        if ($this->customFields !== null) {
            foreach ($this->customFields as $key => $value) {
                if (strlen($key) > 64) {
                    throw new InvalidArgumentException('Custom field names must not exceed 64 characters');
                }
                if (strlen($value) > 1024) {
                    throw new InvalidArgumentException('Custom field values must not exceed 1024 characters');
                }
            }
        }
    }
}
