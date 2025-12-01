<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;

/**
 * EmvKey data transfer object.
 *
 * Represents an EMV (chip card) cryptographic key used by a terminal.
 * EMV keys are read-only and managed by the payment processor.
 *
 * All properties are read-only and only present in API responses.
 */
class EmvKey implements DataTransferObject
{
    use SerializesData;

    public function __construct(
        public readonly ?string $rid = null,
        public readonly ?string $index = null,
        public readonly ?string $modulus = null,
        public readonly ?string $exponent = null,
        public readonly ?string $checksum = null,
    ) {
    }
}
