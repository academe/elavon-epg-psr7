<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * PanToken data transfer object.
 *
 * Converts a Personal Account Number (card number) into a PanToken which is a tokenized
 * version of the data that can safely be stored. This allows your integration to stay PCI compliant.
 * A PanToken is unique to an individual merchantId and PAN combination.
 *
 * Properties marked [Response] are typically only present in API responses.
 * Properties marked [Request] are typically sent in API requests (write-only).
 */
class PanToken implements DataTransferObject
{
    use SerializesData;

    public function __construct(
        public readonly ?string $reference = null,
        public readonly ?string $number = null,
        public readonly ?string $panToken = null,
        public readonly ?bool $success = null,
        public readonly ?int $cardExpirationMonth = null,
        public readonly ?int $cardExpirationYear = null,
    ) {
        $this->validate();
    }

    /**
     * Validates PAN token data.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // Validate reference length and pattern
        if ($this->reference !== null) {
            if (strlen($this->reference) > 255) {
                throw new InvalidArgumentException('Reference must not exceed 255 characters');
            }
            if (!preg_match('/^[^%<>\/\[\]{}\\\\]*$/', $this->reference)) {
                throw new InvalidArgumentException('Reference contains invalid characters');
            }
        }

        // Validate number length and pattern
        if ($this->number !== null) {
            if (strlen($this->number) > 23) {
                throw new InvalidArgumentException('Number must not exceed 23 characters');
            }
            if (!preg_match('/^[\w \-+:()\/ ]*$/', $this->number)) {
                throw new InvalidArgumentException('Number contains invalid characters');
            }
        }

        // Validate cardExpirationMonth
        if ($this->cardExpirationMonth !== null) {
            if ($this->cardExpirationMonth < 1 || $this->cardExpirationMonth > 12) {
                throw new InvalidArgumentException('Card expiration month must be between 1 and 12');
            }
        }

        // Validate cardExpirationYear
        if ($this->cardExpirationYear !== null) {
            if ($this->cardExpirationYear < 2000 || $this->cardExpirationYear > 2099) {
                throw new InvalidArgumentException('Card expiration year must be between 2000 and 2099');
            }
        }

        // Validate that if one expiration field is set, both must be set
        if (($this->cardExpirationMonth !== null && $this->cardExpirationYear === null) ||
            ($this->cardExpirationMonth === null && $this->cardExpirationYear !== null)) {
            throw new InvalidArgumentException('If card expiration is provided, both month and year must be set');
        }
    }
}
