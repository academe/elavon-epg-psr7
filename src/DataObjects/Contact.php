<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\DataObjects;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * Contact data transfer object.
 *
 * Represents contact details including name, company, address, and communication info.
 * Used for shipping (shipTo) and billing (billTo) contact information.
 */
class Contact
{
    /**
     * @param string|null $fullName Full name (max 255 chars)
     * @param string|null $company Company name (max 255 chars)
     * @param string|null $street1 Street line 1 (max 255 chars)
     * @param string|null $street2 Street line 2 (max 255 chars)
     * @param string|null $city City (max 255 chars)
     * @param string|null $region State/Province/Region (max 255 chars)
     * @param string|null $postalCode Zip/Postal code (max 255 chars)
     * @param string|null $countryCode ISO 3166-1 Alpha-3 country code (3 chars)
     * @param string|null $primaryPhone Primary phone (max 255 chars)
     * @param string|null $alternatePhone Alternate phone (max 255 chars)
     * @param string|null $fax Fax number (max 255 chars)
     * @param string|null $email Email address (max 254 chars)
     */
    public function __construct(
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
        public readonly ?string $email = null,
    ) {
        $this->validate();
    }

    /**
     * Creates a Contact instance from an array representation.
     *
     * @param array<string, mixed> $data Array with contact data
     *
     * @throws InvalidArgumentException When data is invalid
     */
    public static function fromArray(array $data): self
    {
        return new self(
            fullName: isset($data['fullName']) ? (string) $data['fullName'] : null,
            company: isset($data['company']) ? (string) $data['company'] : null,
            street1: isset($data['street1']) ? (string) $data['street1'] : null,
            street2: isset($data['street2']) ? (string) $data['street2'] : null,
            city: isset($data['city']) ? (string) $data['city'] : null,
            region: isset($data['region']) ? (string) $data['region'] : null,
            postalCode: isset($data['postalCode']) ? (string) $data['postalCode'] : null,
            countryCode: isset($data['countryCode']) ? (string) $data['countryCode'] : null,
            primaryPhone: isset($data['primaryPhone']) ? (string) $data['primaryPhone'] : null,
            alternatePhone: isset($data['alternatePhone']) ? (string) $data['alternatePhone'] : null,
            fax: isset($data['fax']) ? (string) $data['fax'] : null,
            email: isset($data['email']) ? (string) $data['email'] : null,
        );
    }

    /**
     * Converts the Contact to an array representation.
     *
     * Only includes non-null values for cleaner JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];

        $properties = [
            'fullName', 'company', 'street1', 'street2', 'city', 'region',
            'postalCode', 'countryCode', 'primaryPhone', 'alternatePhone', 'fax', 'email',
        ];

        foreach ($properties as $prop) {
            if ($this->$prop !== null) {
                $data[$prop] = $this->$prop;
            }
        }

        return $data;
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

        // Validate email length (if present)
        if ($this->email !== null && strlen($this->email) > 254) {
            throw new InvalidArgumentException(
                'Email address must not exceed 254 characters'
            );
        }

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
