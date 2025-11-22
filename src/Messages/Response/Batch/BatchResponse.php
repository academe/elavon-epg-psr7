<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\Batch;

use Academe\Elavon\Epg\Psr7\Dtos\Batch;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

/**
 * Batch Response.
 *
 * Parses a PSR-7 response from the EPG API containing either batch data or error details.
 *
 * For successful responses (2xx), contains batch data.
 * For error responses (4xx, 5xx), contains error details.
 */
class BatchResponse
{
    use HandlesErrors;

    private readonly ?Batch $batch;

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
            $this->batch = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->batch = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    /**
     * Creates a BatchResponse from a PSR-7 response.
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
     * Gets the parsed Batch object.
     *
     * Only available for successful responses (2xx status codes).
     *
     * @return Batch|null Returns null if response was an error
     */
    public function getBatch(): ?Batch
    {
        return $this->batch;
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
     * Parses a successful response into a Batch object.
     *
     * @return Batch
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseSuccessResponse(): Batch
    {
        $data = $this->parseJsonBody();
        return Batch::fromData($data);
    }

}
