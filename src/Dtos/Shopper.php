<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * Shopper data transfer object.
 *
 * Details for a Shopper that the Merchant wants to remember for later use.
 * Other resources may then reference this shopper.
 *
 * All properties are read-only.
 */
class Shopper implements DataTransferObject
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
            'object' => ['primaryAddress'],
            'string' => [
                'href', 'id', 'createdAt', 'modifiedAt', 'deletedAt', 'merchant',
                'defaultStoredCard', 'defaultStoredAchPayment', 'fullName', 'description',
                'company', 'primaryPhone', 'alternatePhone', 'fax', 'email', 'customReference',
            ],
            'array' => ['customFields'],
        ];
    }

    /**
     * @param string|null $href Shopper Resource URL (self link)
     * @param string|null $id Shopper Resource ID
     * @param string|null $createdAt Creation timestamp
     * @param string|null $modifiedAt Modification timestamp
     * @param string|null $deletedAt Deletion timestamp
     * @param string|null $merchant Merchant Resource URL
     * @param string|null $defaultStoredCard Default StoredCard Resource URL
     * @param string|null $defaultStoredAchPayment Default StoredAchPayment Resource URL
     * @param string|null $fullName Shopper full name (required for creation)
     * @param string|null $description Shopper description
     * @param string|null $company Company name
     * @param Contact|array<string, mixed>|null $primaryAddress Primary address
     * @param string|null $primaryPhone Primary phone number
     * @param string|null $alternatePhone Alternate phone number
     * @param string|null $fax Fax number
     * @param string|null $email Email address
     * @param string|null $customReference Custom reference
     * @param array<string, mixed>|null $customFields Custom fields
     */
    public function __construct(
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $modifiedAt = null,
        public readonly ?string $deletedAt = null,
        public readonly ?string $merchant = null,
        public readonly ?string $defaultStoredCard = null,
        public readonly ?string $defaultStoredAchPayment = null,
        public readonly ?string $fullName = null,
        public readonly ?string $description = null,
        public readonly ?string $company = null,
        public readonly ?Contact $primaryAddress = null,
        public readonly ?string $primaryPhone = null,
        public readonly ?string $alternatePhone = null,
        public readonly ?string $fax = null,
        public readonly ?string $email = null,
        public readonly ?string $customReference = null,
        public readonly ?array $customFields = null,
    ) {
        $this->validate();
    }

    /**
     * Validates shopper data.
     *
     * @throws InvalidArgumentException
     */
    private function validate(): void
    {
        // Email validation if provided
        if ($this->email !== null && strlen($this->email) > 254) {
            throw new InvalidArgumentException('Email address cannot exceed 254 characters');
        }
    }
}
