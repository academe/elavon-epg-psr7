<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * BLIK data transfer object.
 *
 * Represents a BLIK payment code (Polish mobile payment system).
 * The code is a six-digit value that is not returned in responses.
 */
class Blik implements DataTransferObject
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
            'string' => ['code'],
        ];
    }

    public function __construct(
        public readonly string $code,
    ) {
        $this->validate();
    }

    /**
     * Validates BLIK code.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        if (!preg_match('/^\d{6}$/', $this->code)) {
            throw new InvalidArgumentException('BLIK code must be exactly 6 digits');
        }
    }
}
