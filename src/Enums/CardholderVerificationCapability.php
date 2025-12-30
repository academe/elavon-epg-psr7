<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Cardholder Verification Capability Enum.
 *
 * Indicates the terminal's capability for cardholder verification.
 */
enum CardholderVerificationCapability: string
{
    case PIN_ENCIPHERED_ONLINE = 'pinEncipheredOnline';
    case PIN_ENCIPHERED_OFFLINE = 'pinEncipheredOffline';
    case PIN_PLAINTEXT_OFFLINE = 'pinPlaintextOffline';
    case SIGNATURE = 'signature';
    case NO_CVM = 'noCvm';
}
