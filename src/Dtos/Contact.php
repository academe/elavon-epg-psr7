<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\ValueObjects\EmailAddress;

/**
 * Contact data transfer object.
 *
 * Represents contact details including name, company, address, and communication info.
 * Used for shipping (shipTo) and billing (billTo) contact information.
 */
class Contact implements DataTransferObject
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
            'object' => [
                'email',
            ],
            'string' => [
                'fullName', 'company', 'street1', 'street2', 'city', 'region',
                'postalCode', 'countryCode', 'primaryPhone', 'alternatePhone', 'fax',
            ],
        ];
    }

    // countryCode: ISO 3166-1 Alpha-3 (3 chars)
    public function __construct(
        public readonly ?EmailAddress $email = null,
        public readonly ?string $fullName = null,
        public readonly ?string $company = null,
        public readonly ?string $street1 = null,
        public readonly ?string $street2 = null,
        public readonly ?string $city = null,
        public readonly ?string $region = null,
        public readonly ?string $postalCode = null,
        public readonly ?string $countryCode = null,
        public readonly ?string $primaryPhone = null,
        public readonly ?string $alternatePhone = null,
        public readonly ?string $fax = null,
    ) {
        $this->validate();
    }

    /**
     * Validates contact data.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // Validate country code format (if present)
        if ($this->countryCode !== null && strlen($this->countryCode) !== 3) {
            throw new InvalidArgumentException(
                "Country code must be exactly 3 characters, got: '{$this->countryCode}'"
            );
        }

        // Email validation is handled by EmailAddress value object

        // Validate other field lengths
        $maxLengthFields = [
            'fullName' => 255,
            'company' => 255,
            'street1' => 255,
            'street2' => 255,
            'city' => 255,
            'region' => 255,
            'postalCode' => 255,
            'primaryPhone' => 255,
            'alternatePhone' => 255,
            'fax' => 255,
        ];

        foreach ($maxLengthFields as $field => $maxLength) {
            if ($this->$field !== null && strlen($this->$field) > $maxLength) {
                throw new InvalidArgumentException(
                    "{$field} must not exceed {$maxLength} characters"
                );
            }
        }
    }
}
