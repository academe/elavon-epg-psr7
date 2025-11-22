<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\ManualBatch;

use Academe\Elavon\Epg\Psr7\Dtos\ManualBatch;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

/**
 * Manual Batch Response.
 *
 * Parses PSR-7 responses for manual batch operations (create, retrieve, update).
 *
 * Example usage:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Response\ManualBatch\ManualBatchResponse;
 *
 * // Parse response from API
 * $response = ManualBatchResponse::fromPsr7Response($psrResponse);
 *
 * if ($response->isSuccessful()) {
 *     $manualBatch = $response->getManualBatch();
 *     echo "Manual batch ID: " . $manualBatch->id;
 * } else {
 *     $error = $response->getError();
 *     echo "Error: " . $error->message;
 * }
 * ```
 */
class ManualBatchResponse
{
    use HandlesErrors;

    private readonly ?ManualBatch $manualBatch;

    /**
     * @param ResponseInterface $response PSR-7 HTTP response
     */
    public function __construct(private readonly ResponseInterface $response)
    {
        // Parse response based on status code
        if ($this->isSuccessful()) {
            $this->manualBatch = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->manualBatch = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    /**
     * Creates a ManualBatchResponse from a PSR-7 response.
     *
     * @param ResponseInterface $response PSR-7 HTTP response
     * @return static
     */
    public static function fromPsr7Response(ResponseInterface $response): static
    {
        return new static($response);
    }

    /**
     * Gets the manual batch from a successful response.
     *
     * @return ManualBatch|null Manual batch on success, null on error
     */
    public function getManualBatch(): ?ManualBatch
    {
        return $this->manualBatch;
    }

    /**
     * Gets the PSR-7 response.
     *
     * @return ResponseInterface
     */
    public function getPsr7Response(): ResponseInterface
    {
        return $this->response;
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
     * Parses a successful response into a ManualBatch object.
     *
     * @return ManualBatch
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseSuccessResponse(): ManualBatch
    {
        $data = $this->parseJsonBody();
        return ManualBatch::fromData($data);
    }

}
