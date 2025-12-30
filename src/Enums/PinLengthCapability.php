<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * PIN Length Capability Enum.
 *
 * Indicates the maximum PIN length the terminal supports.
 */
enum PinLengthCapability: string
{
    case UNKNOWN = 'unknown';
    case NONE = 'none';
    case FOUR = 'four';
    case FIVE = 'five';
    case SIX = 'six';
    case SEVEN = 'seven';
    case EIGHT = 'eight';
    case NINE = 'nine';
    case TEN = 'ten';
    case ELEVEN = 'eleven';
    case TWELVE = 'twelve';
}