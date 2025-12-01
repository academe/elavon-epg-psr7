<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * Debtor Account data transfer object.
 *
 * Account information required for MCC 6012/6050/6051 merchants.
 * All fields are write-only and not returned in responses.
 */
class DebtorAccount implements DataTransferObject
{
    use SerializesData;

    // dateOfBirth: YYYYMMDD format
    public function __construct(
        public readonly ?string $dateOfBirth = null,
        public readonly ?string $accountNumber = null,
        public readonly ?string $postalCode = null,
        public readonly ?string $lastName = null,
    ) {
        $this->validate();
    }

    /**
     * Validates debtor account data.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // Validate date of birth format (YYYYMMDD)
        if ($this->dateOfBirth !== null && !preg_match('/^\d{8}$/', $this->dateOfBirth)) {
            throw new InvalidArgumentException('Date of birth must be in YYYYMMDD format');
        }

        // Validate account number length (1-10 chars) and pattern
        if ($this->accountNumber !== null) {
            $length = strlen($this->accountNumber);
            if ($length < 1 || $length > 10) {
                throw new InvalidArgumentException('Account number must be between 1 and 10 characters');
            }
            if (preg_match('/[%<>\/\[\]{}\\\\]/', $this->accountNumber)) {
                throw new InvalidArgumentException('Account number contains invalid characters');
            }
        }

        // Validate postal code pattern
        if ($this->postalCode !== null) {
            if (strlen($this->postalCode) > 255) {
                throw new InvalidArgumentException('Postal code must not exceed 255 characters');
            }
            if (preg_match('/[%<>\/\[\]{}\\\\]/', $this->postalCode)) {
                throw new InvalidArgumentException('Postal code contains invalid characters');
            }
        }

        // Validate last name
        if ($this->lastName !== null) {
            if (strlen($this->lastName) < 1) {
                throw new InvalidArgumentException('Last name must not be empty');
            }
            if (strlen($this->lastName) > 255) {
                throw new InvalidArgumentException('Last name must not exceed 255 characters');
            }
            if (preg_match('/[%<>\/\[\]{}\\\\]/', $this->lastName)) {
                throw new InvalidArgumentException('Last name contains invalid characters');
            }
        }
    }
}
