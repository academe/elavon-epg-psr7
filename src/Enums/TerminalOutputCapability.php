<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * Terminal Output Capability Enum.
 *
 * Indicates the terminal's output capability.
 */
enum TerminalOutputCapability: string
{
    case PRINTER = 'printer';
    case DISPLAY = 'display';
}
