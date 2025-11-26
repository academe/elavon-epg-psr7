<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

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

    /** @var array<Region>|null */
    public readonly ?array $regions;

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

    /**
     * @param string|null $href Merchant Resource URL (self link)
     * @param string|null $id Merchant Resource ID assigned by server
     * @param string|null $legalName Legal name under which the merchant operates
     * @param string|null $friendlyName Friendly name assigned to the merchant
     * @param array<Region|string>|null $regions Regions in which the merchant operates (NA/EU)
     * @param bool|null $isDemo Is this a demo merchant for evaluation purposes only?
     */
    public function __construct(
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?string $legalName = null,
        public readonly ?string $friendlyName = null,
        array|null $regions = null,
        public readonly ?bool $isDemo = null,
    ) {
        // Normalize Region array
        $this->regions = $this->normalizeEnumArray($regions, Region::class);
    }
}
