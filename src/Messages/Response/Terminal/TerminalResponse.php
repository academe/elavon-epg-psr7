<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\Terminal;

use Academe\Elavon\Epg\Psr7\Contracts\ResponseMessage;
use Academe\Elavon\Epg\Psr7\Dtos\Terminal;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

/**
 * Terminal Response.
 *
 * Parses a PSR-7 response from the EPG API containing either terminal data or error details.
 *
 * For successful responses (2xx), contains terminal data.
 * For error responses (4xx, 5xx), contains error details.
 */
class TerminalResponse implements ResponseMessage
{
    use ParsesPsr7Response;

    public readonly ?Terminal $terminal;

    /**
     * @param array<string, mixed> $data Parsed response body data
     * @param int $statusCode HTTP status code
     *
     * @throws InvalidArgumentException When response cannot be parsed
     */
    public function __construct(array $data, int $statusCode) {
        $this->statusCode = $statusCode;

        // Parse response based on status code
        if ($this->isSuccessful()) {
            $this->terminal = Terminal::fromData($data);
            $this->error = null;
        } else {
            $this->terminal = null;
            $this->error = self::parseErrorData($data);
        }
    }
}
