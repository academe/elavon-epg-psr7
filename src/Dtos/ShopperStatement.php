<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * Shopper statement data transfer object.
 *
 * Dynamic overrides of what might appear on a shopper's statement.
 * All properties are read-only.
 */
class ShopperStatement implements DataTransferObject
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
            'string' => ['name', 'phone', 'url'],
        ];
    }

    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $phone = null,
        public readonly ?string $url = null,
    ) {
        $this->validate();
    }

    /**
     * Validates shopper statement data.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        if ($this->name !== null && strlen($this->name) > 25) {
            throw new InvalidArgumentException(
                'Statement name must not exceed 25 characters'
            );
        }

        if ($this->phone !== null && strlen($this->phone) > 20) {
            throw new InvalidArgumentException(
                'Statement phone must not exceed 20 characters'
            );
        }

        if ($this->url !== null && strlen($this->url) > 13) {
            throw new InvalidArgumentException(
                'Statement URL must not exceed 13 characters'
            );
        }
    }
}
