<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * POS Entry Capability Enum.
 *
 * Indicates the terminal's capability for card data entry.
 */
enum PosEntryCapability: string
{
    case KEY_ENTRY = 'keyEntry';
    case MAGNETIC_STRIPE = 'magneticStripe';
    case EMV_CONTACT = 'emvContact';
    case EMV_CONTACTLESS = 'emvContactless';
}
