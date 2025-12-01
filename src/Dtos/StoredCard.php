<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\CredentialOnFileType;
use Academe\Elavon\Epg\Psr7\Enums\ShopperInteraction;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\ValueObjects\CustomFields;

/**
 * Stored Card data transfer object.
 *
 * Represents a card stored for a shopper that may be used for recurring payments.
 * Stored cards allow merchants to charge customers without requiring them to re-enter card details.
 *
 * Properties marked [Response] are typically only present in API responses.
 * Properties marked [Request] are typically sent in API requests.
 *
 * Note: Implements custom fromData() to handle enum conversion.
 */
class StoredCard implements DataTransferObject
{
    use SerializesData;

    public function __construct(
        public readonly ?Card $card = null,
        public readonly ?string $shopper = null,
        public readonly ?string $hostedCard = null,
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $modifiedAt = null,
        public readonly ?string $deletedAt = null,
        public readonly ?string $merchant = null,
        public readonly ?ShopperInteraction $shopperInteraction = null,
        public readonly ?CredentialOnFileType $credentialOnFileType = null,
        public readonly ?string $paymentMethodLink = null,
        public readonly ?string $paymentMethodSession = null,
        public readonly ?string $customReference = null,
        public readonly ?CustomFields $customFields = null,
    ) {
        $this->validate();
    }

    /**
     * Validates stored card data.
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
