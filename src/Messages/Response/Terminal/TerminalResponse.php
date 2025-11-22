<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\Terminal;

use Academe\Elavon\Epg\Psr7\Dtos\Terminal;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

/**
 * Terminal Response.
 *
 * Parses a PSR-7 response from the EPG API containing either terminal data or error details.
 *
 * For successful responses (2xx), contains terminal data.
 * For error responses (4xx, 5xx), contains error details.
 */
class TerminalResponse
{
    use HandlesErrors;

    private readonly ?Terminal $terminal;

    /**
     * @param ResponseInterface $response PSR-7 response from the API
     *
     * @throws InvalidArgumentException When response cannot be parsed
     */
    public function __construct(
        private readonly ResponseInterface $response,
    ) {
        // Parse response based on status code
        if ($this->isSuccessful()) {
            $this->terminal = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->terminal = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    /**
     * Creates a TerminalResponse from a PSR-7 response.
     *
     * @param ResponseInterface $response PSR-7 response
     *
     * @return self
     * @throws InvalidArgumentException When response cannot be parsed
     */
    public static function fromPsr7Response(ResponseInterface $response): self
    {
        return new self($response);
    }

    /**
     * Gets the parsed Terminal object.
     *
     * Only available for successful responses (2xx status codes).
     *
     * @return Terminal|null Returns null if response was an error
     */
    public function getTerminal(): ?Terminal
    {
        return $this->terminal;
    }

    /**
     * Gets the HTTP status code.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    /**
     * Gets the original PSR-7 response.
     *
     * @return ResponseInterface
     */
    public function getPsr7Response(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Parses a successful response into a Terminal object.
     *
     * @return Terminal
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseSuccessResponse(): Terminal
    {
        $data = $this->parseJsonBody();
        return Terminal::fromData($data);
    }

}
