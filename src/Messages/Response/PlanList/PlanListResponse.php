<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\PlanList;

use Academe\Elavon\Epg\Psr7\Dtos\PlanList;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

/**
 * PlanList Response.
 *
 * Parses a PSR-7 response from the EPG API containing either plan list data or error details.
 *
 * For successful responses (2xx), contains plan list data.
 * For error responses (4xx, 5xx), contains error details.
 */
class PlanListResponse
{
    use HandlesErrors;

    private readonly ?PlanList $planList;

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
            $this->planList = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->planList = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    /**
     * Creates a PlanListResponse from a PSR-7 response.
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
     * Gets the parsed PlanList object.
     *
     * Only available for successful responses (2xx status codes).
     *
     * @return PlanList|null Returns null if response was an error
     */
    public function getPlanList(): ?PlanList
    {
        return $this->planList;
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
     * Parses a successful response into a PlanList object.
     *
     * @return PlanList
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseSuccessResponse(): PlanList
    {
        $data = $this->parseJsonBody();
        return PlanList::fromData($data);
    }

}
