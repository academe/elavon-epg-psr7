<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Order Item Type enumeration.
 *
 * Represents the type of an item in an order.
 */
enum OrderItemType: string
{
    case GOODS = 'goods';
    case SERVICE = 'service';
    case TAX = 'tax';
    case SHIPPING = 'shipping';
    case DISCOUNT = 'discount';
    case UNKNOWN = 'unknown';
}
