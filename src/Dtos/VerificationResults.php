<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\Verification;

/**
 * Verification Results data transfer object.
 *
 * Represents card verification results including CVV, AVS, and 3DS results.
 * All properties are read-only and only present in API responses.
 */
class VerificationResults implements DataTransferObject
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
            'enum' => [
                'name',
                'securityCode',
                'addressStreet',
                'addressPostalCode',
                'threeDSecureV2',
                'cryptogramSecurity',
            ],
        ];
    }

    public function __construct(
        public readonly ?Verification $name = null,
        public readonly ?Verification $securityCode = null,
        public readonly ?Verification $addressStreet = null,
        public readonly ?Verification $addressPostalCode = null,
        public readonly ?Verification $threeDSecureV2 = null,
        public readonly ?Verification $cryptogramSecurity = null,
    ) {
    }
}