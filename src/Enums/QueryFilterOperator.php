<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Filter operators for query parameters.
 *
 * These operators are used when filtering collection/list endpoints.
 * Useful for building UI filter components.
 */
enum QueryFilterOperator: string
{
    case EQ = 'eq';           // equals
    case NE = 'ne';           // not equals
    case GT = 'gt';           // greater than
    case GE = 'ge';           // greater than or equal
    case LT = 'lt';           // less than
    case LE = 'le';           // less than or equal
    case LIKE = 'like';       // like pattern
    case IN = 'in';           // in list
    case CONTAINS = 'contains'; // contains
    case IS = 'is';           // is (for null checks)
    case IS_NOT = 'isnot';    // is not (for null checks)
}