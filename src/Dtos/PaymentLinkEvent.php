<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * PaymentLinkEvent data transfer object.
 *
 * An event for a payment link, such as making a payment or sending an email reminder.
 * All properties are optional as different fields are used in different contexts.
 *
 * Properties marked [Response] are typically only present in API responses.
 * Properties marked [Request] are typically sent in API requests.
 * Properties without markers may appear in both contexts.
 */
class PaymentLinkEvent implements DataTransferObject
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
            'string' => [
                'href', 'id', 'merchant', 'paymentLink', 'type',
                'createdAt', 'createdBy', 'transaction', 'shopperEmailAddress',
            ],
        ];
    }

    /**
     * @param string|null $href [Response] PaymentLinkEvent Resource URL (self link)
     * @param string|null $id [Response] PaymentLinkEvent Resource ID assigned by server
     * @param string|null $merchant [Response] Merchant Resource URL
     * @param string|null $paymentLink PaymentLink Resource URL
     * @param string|null $type Event type (payment, reminderSent, unknown)
     * @param string|null $createdAt [Response] Creation timestamp
     * @param string|null $createdBy Who or what created the event (max 255 chars)
     * @param string|null $transaction [Response] Transaction Resource URL (required if type is 'payment')
     * @param string|null $shopperEmailAddress Shopper's email address (required if type is 'reminderSent')
     *
     * @throws InvalidArgumentException When validation fails
     */
    public function __construct(
        // Response-only fields
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?string $merchant = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $transaction = null,

        // Request/Response fields
        public readonly ?string $paymentLink = null,
        public readonly ?string $type = null,
        public readonly ?string $createdBy = null,
        public readonly ?string $shopperEmailAddress = null,
    ) {
        $this->validate();
    }

    /**
     * Validates payment link event data.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // Validate type
        if ($this->type !== null) {
            $validTypes = ['payment', 'reminderSent', 'unknown'];
            if (!in_array($this->type, $validTypes, true)) {
                throw new InvalidArgumentException("Invalid event type: {$this->type}");
            }
        }

        // Validate createdBy length
        if ($this->createdBy !== null && strlen($this->createdBy) > 255) {
            throw new InvalidArgumentException('Created by must not exceed 255 characters');
        }
    }
}
