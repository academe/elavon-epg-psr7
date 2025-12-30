<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Account Entry Mode Enum.
 *
 * Indicates how the card data was entered into the terminal.
 */
enum AccountEntryMode: string
{
    case KEY_ENTERED = 'keyEntered';
    case MAGNETIC_STRIPE = 'magneticStripe';
    case EMV_CONTACTLESS = 'emvContactless';
    case EMV_CHIP = 'emvChip';
    case EMV_CHIP_WITH_CVV = 'emvChipWithCvv';
    case EMV_CHIP_FALLBACK_TO_MAGNETIC_STRIPE = 'emvChipFallbackToMagneticStripe';
    case EMV_CONTACTLESS_FALLBACK_TO_EMV_CHIP = 'emvContactlessFallbackToEmvChip';
}