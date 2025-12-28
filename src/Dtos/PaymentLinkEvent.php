<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\PaymentLinkEventType;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use DateTimeImmutable;

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

    // Normalized properties (enums)
    public readonly ?PaymentLinkEventType $type;

    public function __construct(
        // Response-only fields
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?string $merchant = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?string $transaction = null,

        // Request/Response fields
        public readonly ?string $paymentLink = null,
        PaymentLinkEventType|string|null $type = null,
        public readonly ?string $createdBy = null,
        public readonly ?string $shopperEmailAddress = null,
    ) {
        // Normalize PaymentLinkEventType enum
        $this->type = match (true) {
            $type instanceof PaymentLinkEventType => $type,
            is_string($type) => PaymentLinkEventType::from($type),
            default => null,
        };

        $this->validate();
    }

    /**
     * Validates payment link event data.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // Validate createdBy length
        if ($this->createdBy !== null && strlen($this->createdBy) > 255) {
            throw new InvalidArgumentException('Created by must not exceed 255 characters');
        }
    }
}
