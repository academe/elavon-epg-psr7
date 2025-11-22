<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\Plan;

use Academe\Elavon\Epg\Psr7\Dtos\Plan;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

/**
 * Plan Response.
 *
 * Parses a PSR-7 response from the EPG API containing either plan data or error details.
 *
 * For successful responses (2xx), contains plan data.
 * For error responses (4xx, 5xx), contains error details.
 */
class PlanResponse
{
    use HandlesErrors;

    private readonly ?Plan $plan;

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
            $this->plan = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->plan = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    /**
     * Creates a PlanResponse from a PSR-7 response.
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
     * Gets the parsed Plan object.
     *
     * Only available for successful responses (2xx status codes).
     *
     * @return Plan|null Returns null if response was an error
     */
    public function getPlan(): ?Plan
    {
        return $this->plan;
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
     * Parses a successful response into a Plan object.
     *
     * @return Plan
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseSuccessResponse(): Plan
    {
        $data = $this->parseJsonBody();
        return Plan::fromData($data);
    }

}
