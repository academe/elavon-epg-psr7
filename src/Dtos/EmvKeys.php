<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\TrueFalseOrUnknown;
use DateTimeImmutable;

/**
 * EMV Keys data transfer object.
 *
 * Date information for EMV keys.
 */
class EmvKeys implements DataTransferObject
{
    use SerializesData;

    public function __construct(
        public readonly ?DateTimeImmutable $clientLastUpdatedAt = null,
        public readonly ?DateTimeImmutable $hostLastUpdatedAt = null,
        public readonly ?TrueFalseOrUnknown $isUpdateNeeded = null,
    ) {
    }
}
