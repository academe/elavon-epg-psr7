<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Money\Money;

/**
 * Partial Authorization data transfer object.
 *
 * Contains information about partial authorization when the processor
 * only authorizes a portion of the requested total.
 */
class PartialAuthorization implements DataTransferObject
{
    use SerializesData;

    public function __construct(
        public readonly ?bool $isPartiallyAuthorizable = null,
        public readonly ?bool $isPartiallyAuthorized = null,
        public readonly ?Money $totalRequested = null,
        public readonly ?Money $totalRemaining = null,
    ) {
    }
}