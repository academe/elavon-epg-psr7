<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Cardholder Verification Method Enum.
 *
 * Indicates how the terminal verified the cardholder.
 */
enum CardholderVerificationMethod: string
{
    case NO_CVM_PERFORMED = 'noCvmPerformed';
    case PIN = 'pin';
    case SIGNATURE = 'signature';
    case NO_CVM = 'noCvm';
}