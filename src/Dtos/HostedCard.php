<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * Hosted Card data transfer object.
 *
 * Represents a single-use hosted card for payment.
 * Hosted cards allow secure card data collection without the merchant handling sensitive data.
 *
 * Properties marked [Response] are typically only present in API responses.
 * Properties marked [Request] are typically sent in API requests.
 */
class HostedCard implements DataTransferObject
{
    use SerializesData;

    // Normalized properties (objects)
    public readonly ?Card $card;

    /**
     * Get property type definitions for this DTO.
     *
     * @return array<string, array<string>>
     */
    public static function getPropertyTypes(): array
    {
        return [
            'object' => ['card'],
            'string' => ['href', 'id', 'createdAt', 'modifiedAt', 'expiresAt', 'merchant', 'customReference'],
            'boolean' => ['doVerify'],
            'array' => ['customFields'],
        ];
    }

    /**
     * @param Card|array<string, mixed>|null $card Card details (required for creation)
     * @param string|null $href [Response] Resource URL (self link)
     * @param string|null $id [Response] HostedCard ID assigned by server
     * @param string|null $createdAt [Response] Creation timestamp (ISO 8601)
     * @param string|null $modifiedAt [Response] Modification timestamp (ISO 8601)
     * @param string|null $expiresAt [Response] Expiration timestamp (ISO 8601)
     * @param string|null $merchant [Response] Merchant resource URL
     * @param bool|null $doVerify [Response] Whether card was verified
     * @param string|null $customReference Optional merchant reference (max 255 chars)
     * @param array<string, string>|null $customFields Custom fields (key-value pairs)
     *
     * @throws InvalidArgumentException When validation fails
     */
    public function __construct(
        Card|array|null $card = null,
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $modifiedAt = null,
        public readonly ?string $expiresAt = null,
        public readonly ?string $merchant = null,
        public readonly ?bool $doVerify = null,
        public readonly ?string $customReference = null,
        public readonly ?array $customFields = null,
    ) {
        // Normalize Card
        $this->card = match (true) {
            $card instanceof Card => $card,
            is_array($card) => Card::fromData($card),
            default => null,
        };

        $this->validate();
    }

    /**
     * Validates hosted card data.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // Validate custom reference length
        if ($this->customReference !== null && strlen($this->customReference) > 255) {
            throw new InvalidArgumentException('Custom reference must not exceed 255 characters');
        }

        // Validate custom fields
        if ($this->customFields !== null) {
            foreach ($this->customFields as $key => $value) {
                if (strlen($key) > 64) {
                    throw new InvalidArgumentException('Custom field name must not exceed 64 characters');
                }
                if (strlen($value) > 1024) {
                    throw new InvalidArgumentException('Custom field value must not exceed 1024 characters');
                }
            }
        }
    }
}
