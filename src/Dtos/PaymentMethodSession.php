<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\HppType;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\ValueObjects\CustomFields;
use DateTimeImmutable;

/**
 * PaymentMethodSession data transfer object.
 *
 * A payment session securely collects payment details from the shopper using the hosted payment page
 * and sends them directly to the platform, allowing the merchant to minimize PCI DSS scope.
 *
 * Properties marked [Response] are typically only present in API responses.
 * Properties marked [Request] are typically sent in API requests.
 * Properties without markers may appear in both contexts.
 */
class PaymentMethodSession implements DataTransferObject
{
    use SerializesData;

    public function __construct(
        // Response-only fields
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $modifiedAt = null,
        public readonly ?string $url = null,
        public readonly ?string $merchant = null,
        public readonly ?string $shopper = null,

        // Request/Response fields
        public readonly ?DateTimeImmutable $expiresAt = null,
        public readonly ?string $account = null,
        public readonly ?string $paymentMethodLink = null,
        public readonly ?string $storedCard = null,
        public readonly ?HppType $hppType = null,
        public readonly ?string $returnUrl = null,
        public readonly ?string $cancelUrl = null,
        public readonly ?string $originUrl = null,
        public readonly ?string $defaultLanguageTag = null,
        public readonly ?string $shopperLanguageTag = null,
        public readonly ?bool $doThreeDSecure = null,
        public readonly ?ThreeDSecure $threeDSecure = null,
        public readonly ?Contact $billTo = null,
        public readonly ?string $customReference = null,
        public readonly ?CustomFields $customFields = null,
    ) {
        $this->validate();
    }

    /**
     * Validates payment method session data.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // Validate URL lengths and patterns
        $urlFields = ['returnUrl', 'cancelUrl', 'originUrl'];
        foreach ($urlFields as $field) {
            if ($this->$field !== null) {
                if (strlen($this->$field) > 2048) {
                    throw new InvalidArgumentException("{$field} must not exceed 2048 characters");
                }
                if (!preg_match('#^https?://[^/]{2,}.*$#', $this->$field)) {
                    throw new InvalidArgumentException("{$field} must be a valid http or https URL");
                }
            }
        }

        // Validate language tag lengths
        if ($this->defaultLanguageTag !== null && strlen($this->defaultLanguageTag) > 255) {
            throw new InvalidArgumentException('Default language tag must not exceed 255 characters');
        }

        if ($this->shopperLanguageTag !== null && strlen($this->shopperLanguageTag) > 255) {
            throw new InvalidArgumentException('Shopper language tag must not exceed 255 characters');
        }

        // Validate customReference length
        if ($this->customReference !== null && strlen($this->customReference) > 255) {
            throw new InvalidArgumentException('Custom reference must not exceed 255 characters');
        }

        // customFields validation is handled by CustomFields value object
    }
}
