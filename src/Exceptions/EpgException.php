<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Exceptions;

use Throwable;

/**
 * Base exception interface for all Elavon EPG PSR-7 exceptions.
 *
 * All exceptions thrown by this package implement this interface,
 * making it easy to catch any exception from the package.
 */
interface EpgException extends Throwable
{
}
