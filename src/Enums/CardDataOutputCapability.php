<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Card Data Output Capability Enum.
 *
 * Indicates the terminal's capability for card data output.
 */
enum CardDataOutputCapability: string
{
    case MAGNETIC_STRIPE = 'magneticStripe';
    case EMV_CONTACT = 'emvContact';
    case EMV_CONTACTLESS = 'emvContactless';
}
