<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Mobile POS Acceptance Device Type Enum.
 *
 * Indicates attributes of the mobile device if it is used as a terminal.
 */
enum MobilePosAcceptanceDeviceType: string
{
    case NOT_APPLICABLE = 'notApplicable';
    case DEDICATED_MOBILE_TERMINAL_WITH_DONGLE = 'dedicatedMobileTerminalWithDongle';
    case OFF_THE_SHELF_MOBILE_DEVICE = 'offTheShelfMobileDevice';
}