<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Terminal Type enum.
 *
 * Indicates whether a terminal is hardware or software based.
 */
enum TerminalType: string
{
    case HARDWARE = 'hardware';
    case SOFTWARE = 'software';
}
