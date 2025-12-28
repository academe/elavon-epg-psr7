<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\ValueObjects\CustomFields;
use DateTimeImmutable;

/**
 * Stored ACH Payment data transfer object.
 *
 * Represents an ACH payment stored for a shopper that may be used for recurring payments.
 * Stored ACH payments allow merchants to charge customers without requiring them to re-enter
 * bank account details.
 *
 * Properties marked [Response] are typically only present in API responses.
 * Properties marked [Request] are typically sent in API requests.
 */
class StoredAchPayment implements DataTransferObject
{
    use SerializesData;

    public function __construct(
        public readonly ?AchPayment $achPayment = null,
        public readonly ?string $shopper = null,
        public readonly ?string $hostedAchPayment = null,
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $modifiedAt = null,
        public readonly ?DateTimeImmutable $deletedAt = null,
        public readonly ?string $merchant = null,
        public readonly ?string $customReference = null,
        public readonly ?CustomFields $customFields = null,
    ) {
        $this->validate();
    }

    /**
     * Validates stored ACH payment data.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // Validate custom reference length
        if ($this->customReference !== null && strlen($this->customReference) > 255) {
            throw new InvalidArgumentException('Custom reference must not exceed 255 characters');
        }

        // customFields validation is handled by CustomFields value object
    }
}
