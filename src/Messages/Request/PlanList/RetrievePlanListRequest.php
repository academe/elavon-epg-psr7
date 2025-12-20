<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PlanList;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Retrieve PlanList Request.
 *
 * Builds a PSR-7 request for retrieving a single plan list (GET /plan-lists/{id}).
 *
 * Note: This class builds the base request but does NOT add:
 * - Elavon API headers (Accept, Accept-Version)
 * - Environment configuration (sandbox, production, custom base URI)
 * - Authentication headers (Authorization)
 * Use the ElavonApiFactory to add these.
 */
class RetrievePlanListRequest
{
    use HasPsr17Factories;

    /**
     * @param string $planListId PlanList ID to retrieve     *
     * @throws InvalidArgumentException When plan list ID is empty
     */
    public function __construct(
        private readonly string $planListId
    ) {
        if (empty($this->planListId)) {
            throw new InvalidArgumentException('PlanList ID cannot be empty');
        }
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Use built-in factory if none provided

        // Build PSR-7 GET request
        return $this->getRequestFactory()
            ->createRequest('GET', '/plan-lists/' . $this->planListId);
    }

    /**
     * Gets the plan list ID being retrieved.
     *
     * @return string
     */
    public function getPlanListId(): string
    {
        return $this->planListId;
    }
}
