<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Cardholder Activated Terminal Type Enum.
 *
 * Indicates if the terminal was unattended (e.g., vending machines, gas stations).
 */
enum CardholderActivatedTerminalType: string
{
    case NOT_APPLICABLE = 'notApplicable';
    case LIMITED_AMOUNT_TERMINAL = 'limitedAmountTerminal';
    case AUTOMATED_DISPENSING_MACHINE = 'automatedDispensingMachine';
    case SELF_SERVICE_TERMINAL = 'selfServiceTerminal';
    case IN_FLIGHT_COMMERCE = 'inFlightCommerce';
    case INTERNET = 'internet';
    case TRANSPONDER = 'transponder';
    case REMOTE_INDICATOR = 'remoteIndicator';
}