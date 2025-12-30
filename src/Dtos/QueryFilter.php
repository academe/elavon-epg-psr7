<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\QueryFilterOperator;

/**
 * Represents a single filter condition for query parameters.
 *
 * Usage:
 * ```php
 * $filter = new QueryFilter(
 *     field: 'type',
 *     operator: QueryFilterOperator::EQ,
 *     value: 'refund',
 * );
 * ```
 */
class QueryFilter implements DataTransferObject
{
    use SerializesData;

    public function __construct(
        public readonly string $field,
        public readonly QueryFilterOperator $operator,
        public readonly string $value,
    ) {
    }

    /**
     * Convert to the filter string format used in query parameters.
     *
     * @return string Format: "field_operator_value"
     */
    public function toFilterString(): string
    {
        return sprintf(
            '%s_%s_%s',
            $this->field,
            $this->operator->value,
            $this->value
        );
    }
}