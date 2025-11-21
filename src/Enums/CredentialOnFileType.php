<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Credential On File Type Enum.
 *
 * Represents the type of credential on file usage for card payments.
 *
 * - none: No credential on file
 * - subscription: Fixed amount, scheduled transactions (e.g., monthly membership)
 * - recurring: Variable amount, scheduled transactions (e.g., utility bills)
 * - unscheduled: Unscheduled transactions using stored credentials
 */
enum CredentialOnFileType: string
{
    case NONE = 'none';
    case RECURRING = 'recurring';
    case SUBSCRIPTION = 'subscription';
    case UNSCHEDULED = 'unscheduled';
}
