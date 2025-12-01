<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Money\Money;

/**
 * Count and Total data transfer object.
 *
 * Contains a count of items and their total monetary value.
 * Used in batch settlement information to track credits, debits, and net totals.
 *
 * All properties are read-only and typically returned in API responses.
 */
class CountAndTotal implements DataTransferObject
{
    use SerializesData;

    public function __construct(
        public readonly ?int $count = null,
        public readonly ?Money $total = null,
    ) {
    }
}
