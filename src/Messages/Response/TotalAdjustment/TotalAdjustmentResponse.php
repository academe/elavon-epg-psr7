<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\TotalAdjustment;

use Academe\Elavon\Epg\Psr7\Dtos\TotalAdjustment;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

/**
 * Total Adjustment Response.
 *
 * Parses a PSR-7 response from the EPG API containing either total adjustment data or error details.
 *
 * For successful responses (2xx, 201), contains total adjustment data.
 * For error responses (4xx, 5xx), contains error details.
 *
 * Used for both:
 * - POST /total-adjustments (201 Created)
 * - GET /total-adjustments/{id} (200 OK)
 */
class TotalAdjustmentResponse
{
    use HandlesErrors;

    private readonly ?TotalAdjustment $totalAdjustment;

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
            $this->totalAdjustment = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->totalAdjustment = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    /**
     * Creates a TotalAdjustmentResponse from a PSR-7 response.
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
     * Gets the parsed TotalAdjustment object.
     *
     * Only available for successful responses (2xx status codes).
     *
     * @return TotalAdjustment|null Returns null if response was an error
     */
    public function getTotalAdjustment(): ?TotalAdjustment
    {
        return $this->totalAdjustment;
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
     * Parses a successful response into a TotalAdjustment object.
     *
     * @return TotalAdjustment
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseSuccessResponse(): TotalAdjustment
    {
        $data = $this->parseJsonBody();
        return TotalAdjustment::fromData($data);
    }

}
