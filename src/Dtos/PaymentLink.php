<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\ValueObjects\Money;

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
    public readonly ?Money $total;
    public readonly ?Money $salesTax;
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
            'object' => ['total', 'salesTax', 'debtorAccount'],
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

    /**
     * @param string|null $href [Response] PaymentLink Resource URL (self link)
     * @param string|null $id [Response] PaymentLink Resource ID assigned by server
     * @param string|null $merchant [Response] Merchant Resource URL
     * @param string|null $account Account Resource URL (defaults to merchant)
     * @param string|null $url [Response] External URL shared with the card holder
     * @param string|null $returnUrl URL to redirect to after payment details are collected
     * @param string|null $createdAt [Response] Creation timestamp
     * @param string|null $createdBy [Response] Who or what created the payment link (max 255 chars)
     * @param string|null $modifiedAt [Response] Modification timestamp
     * @param string|null $expiresAt Expiration timestamp (required for creation)
     * @param string|null $cancelledAt [Response] Cancellation timestamp
     * @param string|null $cancelledBy [Response] Who or what cancelled the payment link (max 255 chars)
     * @param bool|null $doCancel Cancel payment link (defaults to false)
     * @param bool|null $doCapture Passed to any transaction created later (defaults to true)
     * @param int|null $conversionCount [Response] Number of authorized transactions created from this PaymentLink
     * @param int|null $conversionLimit Number of times the PaymentLink may be used to complete a Transaction
     * @param string|null $description Descriptive text indicating the purpose of the PaymentLink (max 255 chars)
     * @param Money|array{amount: string, currencyCode: string}|null $total Total payment amount (required for creation)
     * @param Money|array{amount: string, currencyCode: string}|null $salesTax Sales tax
     * @param DebtorAccount|array<string, mixed>|null $debtorAccount Account information required for MCC 6012/6050/6051 merchants
     * @param string|null $orderReference Optional order reference displayed in merchant dashboard (max 255 chars)
     * @param string|null $shopperEmailAddress Shopper's email address (max 254 chars)
     * @param string|null $shopper Shopper Resource URL
     * @param array<string>|null $status [Response] The status of the payment link (active, completed, cancelled, expired)
     * @param bool|null $useStoredPaymentMethod Whether to use stored payment methods
     * @param string|null $customReference Optional reference provided by the merchant (max 255 chars)
     * @param array<string, string>|null $customFields Custom fields (field names max 64 chars, values max 1024 chars)
     *
     * @throws InvalidArgumentException When validation fails
     */
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
        Money|array|null $total = null,
        Money|array|null $salesTax = null,
        DebtorAccount|array|null $debtorAccount = null,
        public readonly ?string $orderReference = null,
        public readonly ?string $shopperEmailAddress = null,
        public readonly ?string $shopper = null,
        public readonly ?bool $useStoredPaymentMethod = null,
        public readonly ?string $customReference = null,
        public readonly ?array $customFields = null,
    ) {
        // Normalize Money objects
        $this->total = match (true) {
            $total instanceof Money => $total,
            is_array($total) => Money::fromData($total),
            default => null,
        };

        $this->salesTax = match (true) {
            $salesTax instanceof Money => $salesTax,
            is_array($salesTax) => Money::fromData($salesTax),
            default => null,
        };

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
