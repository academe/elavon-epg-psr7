<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\ValueObjects\Money;

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

    // Normalized properties (objects)
    public readonly ?Money $total;

    /**
     * Get property type definitions for this DTO.
     *
     * @return array<string, array<string>>
     */
    public static function getPropertyTypes(): array
    {
        return [
            'object' => ['total'],
            'int' => ['count'],
        ];
    }

    /**
     * @param int|null $count Count of items
     * @param Money|array{amount: string, currencyCode: string}|null $total Total monetary amount
     */
    public function __construct(
        public readonly ?int $count = null,
        Money|array|null $total = null,
    ) {
        // Normalize Money object
        $this->total = match (true) {
            $total instanceof Money => $total,
            is_array($total) => Money::fromData($total),
            default => null,
        };
    }
}
