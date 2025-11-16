<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\DataObjects;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * Shopper statement data transfer object.
 *
 * Dynamic overrides of what might appear on a shopper's statement.
 * All properties are read-only.
 */
class ShopperStatement
{
    /**
     * @param string|null $name Statement descriptor name (max 25 chars)
     * @param string|null $phone Statement phone number (max 20 chars)
     * @param string|null $url Statement URL (max 13 chars)
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $phone = null,
        public readonly ?string $url = null,
    ) {
        $this->validate();
    }

    /**
     * Creates a ShopperStatement instance from an array representation.
     *
     * @param array<string, mixed> $data Array with shopper statement data
     *
     * @throws InvalidArgumentException When data is invalid
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: isset($data['name']) ? (string) $data['name'] : null,
            phone: isset($data['phone']) ? (string) $data['phone'] : null,
            url: isset($data['url']) ? (string) $data['url'] : null,
        );
    }

    /**
     * Converts the ShopperStatement to an array representation.
     *
     * Only includes non-null values for cleaner JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->name !== null) {
            $data['name'] = $this->name;
        }

        if ($this->phone !== null) {
            $data['phone'] = $this->phone;
        }

        if ($this->url !== null) {
            $data['url'] = $this->url;
        }

        return $data;
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
