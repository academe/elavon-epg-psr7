<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\SubscriptionState;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * Subscription data transfer object.
 *
 * A subscription associates a shopper with a plan, and details exactly how and when
 * the shopper will be billed.
 *
 * All properties are optional as different fields are used in different contexts.
 * Validation of required fields should occur at the message level (CreateSubscriptionRequest, etc.).
 *
 * Properties marked [Response] are typically only present in API responses.
 * Properties marked [Request] are typically sent in API requests.
 * Properties without markers may appear in both contexts.
 */
class Subscription implements DataTransferObject
{
    use SerializesData;

    // Normalized properties (objects)
    public readonly ?DebtorAccount $debtorAccount;
    public readonly ?SubscriptionSurcharge $surcharge;

    /**
     * Get property type definitions for this DTO.
     *
     * @return array<string, array<string>>
     */
    public static function getPropertyTypes(): array
    {
        return [
            'object' => ['debtorAccount', 'surcharge'],
            'array' => ['customFields'],
            'string' => [
                'href', 'id', 'createdAt', 'modifiedAt', 'merchant', 'plan',
                'shopper', 'account', 'doSendReceipt', 'storedCard', 'storedAchPayment',
                'surchargeAdvice', 'initialSurchargeAdvice', 'timeZoneId',
                'firstBillAt', 'nextBillAt', 'previousBillAt', 'finalBillAt',
                'cancelRequestedAt', 'subscriptionState', 'customReference',
            ],
            'int' => ['billCount', 'cancelAfterBillNumber', 'nextBillNumber', 'failureCount'],
        ];
    }

    /**
     * @param string|null $href [Response] Subscription Resource URL (self link)
     * @param string|null $id [Response] Subscription Resource ID assigned by server
     * @param string|null $createdAt [Response] Creation timestamp
     * @param string|null $modifiedAt [Response] Modification timestamp
     * @param string|null $merchant [Response] Merchant Resource URL
     * @param string|null $plan Plan Resource URL (required for creation) - determines billing details and frequency
     * @param string|null $shopper [Response] Shopper Resource URL
     * @param string|null $account Account Resource URL (defaults to merchant)
     * @param DebtorAccount|array<string, mixed>|null $debtorAccount Account information required for MCC 6012 merchants
     * @param string|bool|null $doSendReceipt Send receipt to shopper's email address (true/false/"DEFAULT" - defaults to "DEFAULT")
     * @param string|null $storedCard StoredCard Resource URL (required for creation) - must belong to the provided Shopper
     * @param string|null $storedAchPayment StoredAchPayment Resource URL - for ACH recurring payments
     * @param string|null $surchargeAdvice [Request] SurchargeAdvice Resource URL obtained through create surchargeAdvice API
     * @param string|null $initialSurchargeAdvice [Request] SurchargeAdvice Resource URL for initial bills
     * @param SubscriptionSurcharge|array<string, mixed>|null $surcharge [Response] Surcharge information if surchargeAdvice was created
     * @param int|null $billCount Total number of bills (minimum 1) - may only be provided if not defined in plan
     * @param string|null $timeZoneId Time zone ID for date fields (IANA Timezone Database Name, e.g., "Europe/London", required for creation)
     * @param string|null $firstBillAt First bill date (YYYY-MM-DD format, required for creation) - anchors billing interval
     * @param string|null $nextBillAt [Response] Next bill date as calculated from first/previous bill date
     * @param string|null $previousBillAt [Response] Most recent bill date, regardless of payment success
     * @param string|null $finalBillAt [Response] Date of final bill if not open-ended
     * @param string|null $cancelRequestedAt [Response] Date when cancel was requested
     * @param int|null $cancelAfterBillNumber The bill number after which no further billings will occur
     * @param int|null $nextBillNumber [Response] Number of the next bill according to plan's schedule
     * @param string|SubscriptionState|null $subscriptionState [Response] Current state of subscription
     * @param int|null $failureCount [Response] Count of consecutive failures performing current payment
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
        public readonly ?string $merchant = null,
        public readonly ?string $shopper = null,
        public readonly ?string $nextBillAt = null,
        public readonly ?string $previousBillAt = null,
        public readonly ?string $finalBillAt = null,
        public readonly ?string $cancelRequestedAt = null,
        public readonly ?int $nextBillNumber = null,
        public readonly string|SubscriptionState|null $subscriptionState = null,
        public readonly ?int $failureCount = null,

        // Request/Response fields
        public readonly ?string $plan = null,
        public readonly ?string $account = null,
        DebtorAccount|array|null $debtorAccount = null,
        public readonly string|bool|null $doSendReceipt = null,
        public readonly ?string $storedCard = null,
        public readonly ?string $storedAchPayment = null,
        public readonly ?string $surchargeAdvice = null,
        public readonly ?string $initialSurchargeAdvice = null,
        SubscriptionSurcharge|array|null $surcharge = null,
        public readonly ?int $billCount = null,
        public readonly ?string $timeZoneId = null,
        public readonly ?string $firstBillAt = null,
        public readonly ?int $cancelAfterBillNumber = null,
        public readonly ?string $customReference = null,
        public readonly ?array $customFields = null,
    ) {
        // Normalize DebtorAccount object
        $this->debtorAccount = match (true) {
            $debtorAccount instanceof DebtorAccount => $debtorAccount,
            is_array($debtorAccount) => DebtorAccount::fromData($debtorAccount),
            default => null,
        };

        // Normalize SubscriptionSurcharge object
        $this->surcharge = match (true) {
            $surcharge instanceof SubscriptionSurcharge => $surcharge,
            is_array($surcharge) => SubscriptionSurcharge::fromData($surcharge),
            default => null,
        };

        $this->validate();
    }

    /**
     * Validates subscription data.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // Validate doSendReceipt is valid value
        if ($this->doSendReceipt !== null && !in_array($this->doSendReceipt, [true, false, 'DEFAULT'], true)) {
            throw new InvalidArgumentException('doSendReceipt must be true, false, or "DEFAULT"');
        }

        // Validate billCount (minimum 1)
        if ($this->billCount !== null && $this->billCount < 1) {
            throw new InvalidArgumentException('Bill count must be at least 1');
        }

        // Validate date format for firstBillAt (YYYY-MM-DD)
        if ($this->firstBillAt !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->firstBillAt)) {
            throw new InvalidArgumentException('First bill date must be in YYYY-MM-DD format');
        }

        // Validate subscriptionState if string
        if (is_string($this->subscriptionState)) {
            try {
                SubscriptionState::from($this->subscriptionState);
            } catch (\ValueError $e) {
                throw new InvalidArgumentException(
                    'Subscription state must be one of: active, completed, cancelled, unpaid, pastDue, unknown',
                    previous: $e
                );
            }
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

    /**
     * Serializes this object to an array for API requests.
     *
     * @return array<string, mixed>
     */
    public function toData(): array
    {
        $data = [];

        foreach (self::getPropertyTypes() as $type => $properties) {
            foreach ($properties as $property) {
                $value = $this->$property ?? null;

                // Skip null values
                if ($value === null) {
                    continue;
                }

                // Handle special serialization
                if ($type === 'object' && method_exists($value, 'toData')) {
                    $data[$property] = $value->toData();
                } elseif ($value instanceof SubscriptionState) {
                    $data[$property] = $value->value;
                } else {
                    $data[$property] = $value;
                }
            }
        }

        return $data;
    }
}
