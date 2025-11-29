<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * HsmCard data transfer object.
 *
 * A single-use card. This captures all payment details so they may be subsequently referenced
 * in a transaction. This resource is recommended for a card present integration.
 *
 * The HsmCard resource is used to hold terminal card data so the data is only needed to be
 * decrypted once. The resource expires as soon as it is used in a transaction or in
 * approximately 30 minutes.
 *
 * Properties marked [Response] are typically only present in API responses.
 * Properties marked [Request] are typically sent in API requests.
 */
class HsmCard implements DataTransferObject
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
            'object' => ['card', 'accountEntryMode', 'deviceInteraction'],
            'array' => ['customFields'],
            'string' => [
                'id', 'createdAt', 'modifiedAt', 'expiresAt',
                'merchant', 'processorAccount', 'terminal', 'customReference',
            ],
        ];
    }

    /**
     * @param string|null $id [Response] Unique identifier assigned by server
     * @param string|null $createdAt [Response] Creation timestamp
     * @param string|null $modifiedAt [Response] Modification timestamp
     * @param string|null $expiresAt [Response] Expiration timestamp
     * @param string|null $merchant [Response] Merchant Resource URL
     * @param string|null $processorAccount [Response] ProcessorAccount Resource URL
     * @param string|null $terminal Terminal Resource URL (required for creation)
     * @param Card|array<string, mixed>|null $card Card data
     * @param mixed $accountEntryMode Account entry mode (required for creation)
     * @param mixed $deviceInteraction Device interaction data
     * @param string|null $customReference Optional reference provided by the merchant (max 255 chars)
     * @param array<string, string>|null $customFields Custom fields (field names max 64 chars, values max 1024 chars)
     *
     * @throws InvalidArgumentException When validation fails
     */
    public function __construct(
        // Response-only fields
        public readonly ?string $id = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $modifiedAt = null,
        public readonly ?string $expiresAt = null,
        public readonly ?string $merchant = null,
        public readonly ?string $processorAccount = null,

        // Request/Response fields
        public readonly ?string $terminal = null,
        public readonly ?Card $card = null,
        public readonly mixed $accountEntryMode = null,
        public readonly mixed $deviceInteraction = null,
        public readonly ?string $customReference = null,
        public readonly ?array $customFields = null,
    ) {
        $this->validate();
    }

    /**
     * Validates HSM card data.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
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
}
