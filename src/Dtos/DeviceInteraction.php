<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\AccountEntryMode;
use Academe\Elavon\Epg\Psr7\Enums\CardDataOutputCapability;
use Academe\Elavon\Epg\Psr7\Enums\CardholderActivatedTerminalType;
use Academe\Elavon\Epg\Psr7\Enums\CardholderVerificationCapability;
use Academe\Elavon\Epg\Psr7\Enums\CardholderVerificationMethod;
use Academe\Elavon\Epg\Psr7\Enums\MobilePosAcceptanceDeviceType;
use Academe\Elavon\Epg\Psr7\Enums\PinLengthCapability;
use Academe\Elavon\Epg\Psr7\Enums\PosEntryCapability;
use Academe\Elavon\Epg\Psr7\Enums\TerminalOutputCapability;

/**
 * Device Interaction data transfer object.
 *
 * Information when using a hardware terminal.
 */
class DeviceInteraction implements DataTransferObject
{
    use SerializesData;

    public function __construct(
        // EMV data
        public readonly ?Emv $emv = null,

        // Entry mode
        public readonly ?AccountEntryMode $accountEntryMode = null,

        // Terminal capabilities
        public readonly ?PosEntryCapability $posEntryCapabilities = null,

        // Card presence flags
        public readonly ?bool $isCardPresent = null,
        public readonly ?bool $isAttendedTerminal = null,

        // Cardholder verification
        public readonly ?CardholderVerificationMethod $cardholderVerificationMethod = null,
        public readonly ?CardholderVerificationCapability $cardholderVerificationCapabilities = null,

        // PIN capability
        public readonly ?PinLengthCapability $pinLengthCapability = null,

        // Output capabilities
        public readonly ?CardDataOutputCapability $cardDataOutputCapabilities = null,
        public readonly ?TerminalOutputCapability $terminalOutputCapabilities = null,

        // Terminal type
        public readonly ?CardholderActivatedTerminalType $cardholderActivatedTerminalType = null,

        // Mobile POS
        public readonly ?MobilePosAcceptanceDeviceType $mobilePosAcceptanceDeviceType = null,
    ) {
    }
}