<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

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

    // Normalized properties (objects)
    public readonly ?AchPayment $achPayment;

    /**
     * Get property type definitions for this DTO.
     *
     * @return array<string, array<string>>
     */
    public static function getPropertyTypes(): array
    {
        return [
            'object' => ['achPayment'],
            'string' => [
                'href', 'id', 'createdAt', 'modifiedAt', 'deletedAt',
                'merchant', 'shopper', 'hostedAchPayment',
                'customReference',
            ],
            'array' => ['customFields'],
        ];
    }

    /**
     * @param AchPayment|array<string, mixed>|null $achPayment ACH payment details
     * @param string|null $shopper [Request/Response] Shopper resource URL (required for creation)
     * @param string|null $hostedAchPayment [Request] HostedAchPayment resource URL (for initialization)
     * @param string|null $href [Response] Resource URL (self link)
     * @param string|null $id [Response] StoredAchPayment ID assigned by server
     * @param string|null $createdAt [Response] Creation timestamp (ISO 8601)
     * @param string|null $modifiedAt [Response] Modification timestamp (ISO 8601)
     * @param string|null $deletedAt [Response] Deletion timestamp (ISO 8601)
     * @param string|null $merchant [Response] Merchant resource URL
     * @param string|null $customReference Optional merchant reference (max 255 chars)
     * @param array<string, string>|null $customFields Custom fields (key-value pairs)
     *
     * @throws InvalidArgumentException When validation fails
     */
    public function __construct(
        AchPayment|array|null $achPayment = null,
        public readonly ?string $shopper = null,
        public readonly ?string $hostedAchPayment = null,
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $modifiedAt = null,
        public readonly ?string $deletedAt = null,
        public readonly ?string $merchant = null,
        public readonly ?string $customReference = null,
        public readonly ?array $customFields = null,
    ) {
        // Normalize AchPayment
        $this->achPayment = match (true) {
            $achPayment instanceof AchPayment => $achPayment,
            is_array($achPayment) => AchPayment::fromData($achPayment),
            default => null,
        };

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
