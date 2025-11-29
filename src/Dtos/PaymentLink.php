<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Money\Money;

/**
 * PaymentLink data transfer object.
 *
 * A PaymentLink contains the details necessary to create a Transaction via HPP.
 * All properties are optional as different fields are used in different contexts.
 * Validation of required fields should occur at the message level (CreatePaymentLinkRequest, etc.).
 *
 * Properties marked [Response] are typically only present in API responses.
 * Properties marked [Request] are typically sent in API requests.
 * Properties without markers may appear in both contexts.
 */
class PaymentLink implements DataTransferObject
{
    use SerializesData;

    // Normalized properties (objects)
    public readonly ?DebtorAccount $debtorAccount;

    /** @var array<string>|null */
    public readonly ?array $status;

    /**
     * Get property type definitions for this DTO.
     *
     * @return array<string, array<string>>
     */
    public static function getPropertyTypes(): array
    {
        return [
            'money' => ['total', 'salesTax'],
            'object' => ['debtorAccount'],
            'array' => ['status', 'customFields'],
            'string' => [
                'href', 'id', 'merchant', 'account', 'url', 'returnUrl',
                'createdAt', 'createdBy', 'modifiedAt', 'expiresAt',
                'cancelledAt', 'cancelledBy', 'description', 'orderReference',
                'shopperEmailAddress', 'shopper', 'customReference',
            ],
            'boolean' => ['doCancel', 'doCapture', 'useStoredPaymentMethod'],
            'int' => ['conversionCount', 'conversionLimit'],
        ];
    }

    public function __construct(
        // Response-only fields
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?string $merchant = null,
        public readonly ?string $url = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $createdBy = null,
        public readonly ?string $modifiedAt = null,
        public readonly ?string $cancelledAt = null,
        public readonly ?string $cancelledBy = null,
        public readonly ?int $conversionCount = null,
        ?array $status = null,

        // Request/Response fields
        public readonly ?string $account = null,
        public readonly ?string $returnUrl = null,
        public readonly ?string $expiresAt = null,
        public readonly ?bool $doCancel = null,
        public readonly ?bool $doCapture = null,
        public readonly ?int $conversionLimit = null,
        public readonly ?string $description = null,
        public readonly ?Money $total = null,
        public readonly ?Money $salesTax = null,
        DebtorAccount|array|null $debtorAccount = null,
        public readonly ?string $orderReference = null,
        public readonly ?string $shopperEmailAddress = null,
        public readonly ?string $shopper = null,
        public readonly ?bool $useStoredPaymentMethod = null,
        public readonly ?string $customReference = null,
        public readonly ?array $customFields = null,
    ) {
        // Normalize DebtorAccount object
        $this->debtorAccount = match (true) {
            $debtorAccount instanceof DebtorAccount => $debtorAccount,
            is_array($debtorAccount) => DebtorAccount::fromData($debtorAccount),
            default => null,
        };

        // Normalize status array
        $this->status = $status;

        $this->validate();
    }

    /**
     * Validates payment link data.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // Validate returnUrl length and pattern
        if ($this->returnUrl !== null) {
            if (strlen($this->returnUrl) > 2048) {
                throw new InvalidArgumentException('Return URL must not exceed 2048 characters');
            }
            if (!preg_match('/^https?:\/\/[^\/]{2,}.*/', $this->returnUrl)) {
                throw new InvalidArgumentException('Return URL must be a valid HTTP/HTTPS URL');
            }
        }

        // Validate createdBy length
        if ($this->createdBy !== null && strlen($this->createdBy) > 255) {
            throw new InvalidArgumentException('Created by must not exceed 255 characters');
        }

        // Validate cancelledBy length
        if ($this->cancelledBy !== null && strlen($this->cancelledBy) > 255) {
            throw new InvalidArgumentException('Cancelled by must not exceed 255 characters');
        }

        // Validate description length
        if ($this->description !== null && strlen($this->description) > 255) {
            throw new InvalidArgumentException('Description must not exceed 255 characters');
        }

        // Validate orderReference length
        if ($this->orderReference !== null && strlen($this->orderReference) > 255) {
            throw new InvalidArgumentException('Order reference must not exceed 255 characters');
        }

        // Validate shopperEmailAddress length
        if ($this->shopperEmailAddress !== null && strlen($this->shopperEmailAddress) > 254) {
            throw new InvalidArgumentException('Shopper email address must not exceed 254 characters');
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

        // Validate status values
        if ($this->status !== null) {
            $validStatuses = ['active', 'completed', 'cancelled', 'expired'];
            foreach ($this->status as $statusValue) {
                if (!in_array($statusValue, $validStatuses, true)) {
                    throw new InvalidArgumentException("Invalid status value: {$statusValue}");
                }
            }
        }
    }
}
