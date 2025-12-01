<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\PaymentLinkStatus;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\ValueObjects\CustomFields;

/**
 * PaymentMethodLink data transfer object.
 *
 * A link containing a URL that may be provided to shoppers in order to capture payment method
 * using the HPP without requiring a transaction.
 *
 * Properties marked [Response] are typically only present in API responses.
 * Properties marked [Request] are typically sent in API requests.
 * Properties without markers may appear in both contexts.
 */
class PaymentMethodLink implements DataTransferObject
{
    use SerializesData;

    public function __construct(
        // Response-only fields
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?string $merchant = null,
        public readonly ?string $account = null,
        public readonly ?string $url = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $modifiedAt = null,
        public readonly ?string $cancelledAt = null,

        // Request/Response fields
        public readonly ?string $returnUrl = null,
        public readonly ?string $expiresAt = null,
        public readonly ?string $cancelledBy = null,
        public readonly ?bool $doCancel = null,
        public readonly ?string $description = null,
        public readonly ?string $shopper = null,
        public readonly ?array $status = null,
        public readonly ?string $customReference = null,
        public readonly ?CustomFields $customFields = null,
    ) {
        $this->validate();
    }

    /**
     * Validates payment method link data.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // Validate returnUrl length
        if ($this->returnUrl !== null && strlen($this->returnUrl) > 2048) {
            throw new InvalidArgumentException('Return URL must not exceed 2048 characters');
        }

        // Validate returnUrl pattern
        if ($this->returnUrl !== null && !preg_match('#^https?://[^/]{2,}.*$#', $this->returnUrl)) {
            throw new InvalidArgumentException('Return URL must be a valid http or https URL');
        }

        // Validate description length
        if ($this->description !== null && strlen($this->description) > 255) {
            throw new InvalidArgumentException('Description must not exceed 255 characters');
        }

        // Validate cancelledBy length
        if ($this->cancelledBy !== null && strlen($this->cancelledBy) > 255) {
            throw new InvalidArgumentException('Cancelled by must not exceed 255 characters');
        }

        // Validate customReference length
        if ($this->customReference !== null && strlen($this->customReference) > 255) {
            throw new InvalidArgumentException('Custom reference must not exceed 255 characters');
        }

        // Validate status array items
        if ($this->status !== null) {
            foreach ($this->status as $statusValue) {
                if (!is_string($statusValue)) {
                    throw new InvalidArgumentException('Status array must contain only strings');
                }
                // Validate against PaymentLinkStatus enum
                try {
                    PaymentLinkStatus::from($statusValue);
                } catch (\ValueError $e) {
                    throw new InvalidArgumentException(
                        "Invalid status value: {$statusValue}",
                        previous: $e
                    );
                }
            }
        }

        // customFields validation is handled by CustomFields value object
    }
}
