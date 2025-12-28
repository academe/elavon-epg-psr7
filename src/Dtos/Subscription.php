<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\SubscriptionState;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\ValueObjects\CustomFields;
use DateTimeImmutable;

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

    // firstBillAt: YYYY-MM-DD format, timeZoneId: IANA timezone name
    public function __construct(
        // Response-only fields
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $modifiedAt = null,
        public readonly ?string $merchant = null,
        public readonly ?string $shopper = null,
        public readonly ?DateTimeImmutable $nextBillAt = null,
        public readonly ?DateTimeImmutable $previousBillAt = null,
        public readonly ?DateTimeImmutable $finalBillAt = null,
        public readonly ?DateTimeImmutable $cancelRequestedAt = null,
        public readonly ?int $nextBillNumber = null,
        public readonly string|SubscriptionState|null $subscriptionState = null,
        public readonly ?int $failureCount = null,

        // Request/Response fields
        public readonly ?string $plan = null,
        public readonly ?string $account = null,
        public readonly ?DebtorAccount $debtorAccount = null,
        public readonly string|bool|null $doSendReceipt = null,
        public readonly ?string $storedCard = null,
        public readonly ?string $storedAchPayment = null,
        public readonly ?string $surchargeAdvice = null,
        public readonly ?string $initialSurchargeAdvice = null,
        public readonly ?SubscriptionSurcharge $surcharge = null,
        public readonly ?int $billCount = null,
        public readonly ?string $timeZoneId = null,
        public readonly ?string $firstBillAt = null,
        public readonly ?int $cancelAfterBillNumber = null,
        public readonly ?string $customReference = null,
        public readonly ?CustomFields $customFields = null,
    ) {
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

        // customFields validation is handled by CustomFields value object
    }
}
