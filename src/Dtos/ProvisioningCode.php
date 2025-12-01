<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;

/**
 * ProvisioningCode data transfer object.
 *
 * Represents a provisioning code used to activate/configure a terminal.
 * Provisioning codes are time-limited and read-only.
 *
 * All properties are read-only and only present in API responses.
 */
class ProvisioningCode implements DataTransferObject
{
    use SerializesData;

    public function __construct(
        public readonly ?string $terminal = null,
        public readonly ?string $code = null,
        public readonly ?string $expiresAt = null,
    ) {
    }
}
