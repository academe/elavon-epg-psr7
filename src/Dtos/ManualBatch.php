<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * Manual Batch data transfer object.
 *
 * Represents a settlement batch. Transactions are captured into a batch and submitted
 * together for settlement and subsequent funding. Manual batches support create/update
 * operations unlike regular read-only batches.
 *
 * Properties marked [Response] are typically only present in API responses.
 * Properties marked [Request] are typically sent in API requests.
 */
class ManualBatch implements DataTransferObject
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
            'string' => ['href', 'id', 'createdAt', 'modifiedAt', 'merchant', 'customReference'],
            'array' => ['customFields'],
        ];
    }

    /**
     * @param string|null $href [Response] Resource URL (self link)
     * @param string|null $id [Response] ManualBatch ID assigned by server
     * @param string|null $createdAt [Response] Creation timestamp (ISO 8601)
     * @param string|null $modifiedAt [Response] Modification timestamp (ISO 8601)
     * @param string|null $merchant [Response] Merchant resource URL
     * @param string|null $customReference Optional merchant reference (max 255 chars)
     * @param array<string, string>|null $customFields Custom fields (key-value pairs)
     *
     * @throws InvalidArgumentException When validation fails
     */
    public function __construct(
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $modifiedAt = null,
        public readonly ?string $merchant = null,
        public readonly ?string $customReference = null,
        public readonly ?array $customFields = null,
    ) {
        $this->validate();
    }

    /**
     * Validates manual batch data.
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
