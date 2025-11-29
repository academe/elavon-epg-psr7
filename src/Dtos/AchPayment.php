<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\AchAccountType;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * ACH Payment data transfer object.
 *
 * Represents ACH (Automated Clearing House) payment details.
 * For requests: includes bank routing and account numbers
 * For responses: includes masked data (last4, fingerprint)
 *
 * Note: Implements custom fromData() to handle enum conversion.
 */
class AchPayment implements DataTransferObject
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
            'enum' => ['achAccountType'],
            'string' => [
                'bankRoutingNumber', 'bankAccountNumber', 'bankAccountToken',
                'achFingerprint', 'last4', 'accountName',
            ],
        ];
    }

    public function __construct(
        public readonly AchAccountType $achAccountType,
        public readonly string $accountName,
        public readonly ?string $bankRoutingNumber = null,
        public readonly ?string $bankAccountNumber = null,
        public readonly ?string $bankAccountToken = null,
        public readonly ?string $achFingerprint = null,
        public readonly ?string $last4 = null,
    ) {
        $this->validate();
    }

    /**
     * Validates ACH payment data.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // Validate account name
        if (empty($this->accountName)) {
            throw new InvalidArgumentException('Account name cannot be empty');
        }

        if (strlen($this->accountName) > 255) {
            throw new InvalidArgumentException('Account name must not exceed 255 characters');
        }

        // Validate bank routing number format (9 digits)
        if ($this->bankRoutingNumber !== null) {
            if (!preg_match('/^\d{9}$/', $this->bankRoutingNumber)) {
                throw new InvalidArgumentException('Bank routing number must be exactly 9 digits');
            }
        }

        // Validate bank account number format (5-16 digits)
        if ($this->bankAccountNumber !== null) {
            if (!preg_match('/^\d{5,16}$/', $this->bankAccountNumber)) {
                throw new InvalidArgumentException('Bank account number must be 5 to 16 digits');
            }
        }

        // Validate last4 format (4 digits) if present
        if ($this->last4 !== null) {
            if (!preg_match('/^\d{4}$/', $this->last4)) {
                throw new InvalidArgumentException('Last 4 digits must be exactly 4 digits');
            }
        }
    }
}
