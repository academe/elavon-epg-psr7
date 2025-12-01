<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\TerminalType;

/**
 * Terminal data transfer object.
 *
 * Information about a terminal (hardware or software) used for payment processing.
 * Terminals are read-only via the API.
 *
 * All properties are read-only and only present in API responses.
 */
class Terminal implements DataTransferObject
{
    use SerializesData;

    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $merchant = null,
        public readonly ?string $processorAccount = null,
        public readonly ?string $processorReference = null,
        public readonly ?TerminalType $terminalType = null,
        public readonly ?string $hostLastUpdatedEmvKeysAt = null,
        public readonly ?string $provisionedAt = null,
        public readonly ?string $transactedAt = null,
    ) {
    }
}
