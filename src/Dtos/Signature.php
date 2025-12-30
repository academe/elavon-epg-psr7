<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\TrueFalseOrUnknown;

/**
 * Signature data transfer object.
 *
 * Point of Sale Signature information.
 */
class Signature implements DataTransferObject
{
    use SerializesData;

    public function __construct(
        public readonly ?bool $isExpected = null,
        public readonly ?bool $isAvailable = null,
        public readonly ?TrueFalseOrUnknown $wasBypassed = null,
    ) {
    }
}