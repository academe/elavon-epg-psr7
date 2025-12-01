<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;

/**
 * 3-D Secure v2 data transfer object.
 *
 * Contains authentication data from 3-D Secure processing.
 * All properties are read-only.
 *
 * Note: Uses custom implementation instead of SerializesData trait due to
 * required field validation in fromArray() method.
 */
class ThreeDSecure implements DataTransferObject
{
    use SerializesData;

    public function __construct(
        public readonly string $directoryServerTransactionId,
        public readonly string $transactionStatus,
        public readonly string $protocolVersion,
        public readonly ?string $electronicCommerceIndicator = null,
        public readonly ?string $authenticationValue = null,
    ) {
        $this->validate();
    }

    /**
     * Creates a ThreeDSecure instance from JSON-compatible data.
     *
     * @param mixed $data Array with 3DS data
     *
     * @throws InvalidArgumentException When data is invalid
     */
    public static function fromData(mixed $data): static
    {
        if (!isset($data['directoryServerTransactionId'])) {
            throw new InvalidArgumentException('directoryServerTransactionId is required');
        }

        if (!isset($data['transactionStatus'])) {
            throw new InvalidArgumentException('transactionStatus is required');
        }

        if (!isset($data['protocolVersion'])) {
            throw new InvalidArgumentException('protocolVersion is required');
        }

        return new self(
            directoryServerTransactionId: (string) $data['directoryServerTransactionId'],
            transactionStatus: (string) $data['transactionStatus'],
            protocolVersion: (string) $data['protocolVersion'],
            electronicCommerceIndicator: isset($data['electronicCommerceIndicator'])
                ? (string) $data['electronicCommerceIndicator']
                : null,
            authenticationValue: isset($data['authenticationValue'])
                ? (string) $data['authenticationValue']
                : null,
        );
    }

    /**
     * Converts the ThreeDSecure to JSON-compatible data.
     *
     * Only includes non-null values for cleaner JSON serialization.
     *
     * @return mixed
     */
    public function toData(): mixed
    {
        $data = [
            'directoryServerTransactionId' => $this->directoryServerTransactionId,
            'transactionStatus' => $this->transactionStatus,
            'protocolVersion' => $this->protocolVersion,
        ];

        if ($this->electronicCommerceIndicator !== null) {
            $data['electronicCommerceIndicator'] = $this->electronicCommerceIndicator;
        }

        if ($this->authenticationValue !== null) {
            $data['authenticationValue'] = $this->authenticationValue;
        }

        return $data;
    }

    /**
     * Returns a shallow array of all non-null properties.
     *
     * @return array<string, mixed>
     */
    public function toObjectArray(): array
    {
        return $this->toData(); // For simple string properties, both methods return the same result
    }

    /**
     * Validates 3-D Secure data.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // Validate directory server transaction ID (UUID format)
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[12345][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $this->directoryServerTransactionId)) {
            throw new InvalidArgumentException(
                'Directory server transaction ID must be a valid UUID (RFC 4122 format)'
            );
        }

        // Validate transaction status (Y, N, U, or A)
        if (!preg_match('/^[YNUA]$/', $this->transactionStatus)) {
            throw new InvalidArgumentException(
                'Transaction status must be Y, N, U, or A'
            );
        }

        // Validate protocol version (format: digit.digit.digit)
        if (!preg_match('/^\d+\.\d+\.\d+$/', $this->protocolVersion)) {
            throw new InvalidArgumentException(
                'Protocol version must be in format X.Y.Z (e.g., "2.1.0")'
            );
        }

        // Validate electronic commerce indicator if present
        if ($this->electronicCommerceIndicator !== null) {
            if (!preg_match('/^0?[012567]$/', $this->electronicCommerceIndicator)) {
                throw new InvalidArgumentException(
                    'Electronic commerce indicator must be 0, 1, 2, 5, 6, or 7 (optionally with leading zero)'
                );
            }
        }

        // Validate authentication value if present (must be exactly 28 characters)
        if ($this->authenticationValue !== null) {
            if (strlen($this->authenticationValue) !== 28) {
                throw new InvalidArgumentException(
                    'Authentication value must be exactly 28 characters (20 bytes Base64 encoded)'
                );
            }
        }
    }
}
