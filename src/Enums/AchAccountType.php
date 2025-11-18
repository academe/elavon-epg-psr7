<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * ACH Account Type Enum.
 *
 * Represents the type of ACH bank account.
 */
enum AchAccountType: string
{
    case SAVINGS_PERSONAL = 'savingsPersonal';
    case CHECKING_PERSONAL = 'checkingPersonal';
    case CHECKING_BUSINESS = 'checkingBusiness';
}
