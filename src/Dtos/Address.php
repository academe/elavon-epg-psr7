<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;

/**
 * Address data transfer object.
 *
 * Represents a physical address with street, city, state, postal code, and country.
 * All properties are read-only.
 */
class Address implements DataTransferObject
{
    use SerializesData;

    public function __construct(
        public readonly ?string $street1 = null,
        public readonly ?string $street2 = null,
        public readonly ?string $city = null,
        public readonly ?string $stateOrProvince = null,
        public readonly ?string $postalCode = null,
        public readonly ?string $country = null,
    ) {
    }
}
