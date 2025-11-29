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
