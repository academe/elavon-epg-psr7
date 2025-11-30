<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Attributes\ArrayOf;
use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\Region;

/**
 * Merchant data transfer object.
 *
 * Information about a merchant in the Elavon Payment Gateway.
 * Merchants are read-only and cannot be created or updated via the API.
 *
 * All properties are read-only and only present in API responses.
 */
class Merchant implements DataTransferObject
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
            'string' => ['href', 'id', 'legalName', 'friendlyName'],
            'array' => ['regions'],
            'boolean' => ['isDemo'],
        ];
    }

    public function __construct(
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?string $legalName = null,
        public readonly ?string $friendlyName = null,
        /** @var array<Region>|null */
        #[ArrayOf(Region::class)]
        public readonly ?array $regions = null,
        public readonly ?bool $isDemo = null,
    ) {
    }
}
