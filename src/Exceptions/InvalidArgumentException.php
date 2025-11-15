<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Exceptions;

use InvalidArgumentException as BaseInvalidArgumentException;

/**
 * Exception thrown when an argument does not match the expected type or format.
 */
final class InvalidArgumentException extends BaseInvalidArgumentException implements EpgException
{
}
