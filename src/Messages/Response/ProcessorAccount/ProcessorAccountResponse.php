<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\ProcessorAccount;

use Academe\Elavon\Epg\Psr7\Dtos\ProcessorAccount;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

/**
 * ProcessorAccount Response.
 *
 * Parses a PSR-7 response from the EPG API containing either processor account data or error details.
 *
 * For successful responses (2xx), contains processor account data.
 * For error responses (4xx, 5xx), contains error details.
 */
class ProcessorAccountResponse
{
    use HandlesErrors;

    private readonly ?ProcessorAccount $processorAccount;

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
            $this->processorAccount = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->processorAccount = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    /**
     * Creates a ProcessorAccountResponse from a PSR-7 response.
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
     * Gets the parsed ProcessorAccount object.
     *
     * Only available for successful responses (2xx status codes).
     *
     * @return ProcessorAccount|null Returns null if response was an error
     */
    public function getProcessorAccount(): ?ProcessorAccount
    {
        return $this->processorAccount;
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
     * Parses a successful response into a ProcessorAccount object.
     *
     * @return ProcessorAccount
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseSuccessResponse(): ProcessorAccount
    {
        $data = $this->parseJsonBody();
        return ProcessorAccount::fromData($data);
    }

}
