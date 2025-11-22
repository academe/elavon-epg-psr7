<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PlanList;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Retrieve PlanList Request.
 *
 * Builds a PSR-7 request for retrieving a single plan list (GET /plan-lists/{id}).
 *
 * Note: This class builds the base request but does NOT add:
 * - Elavon API headers (Accept, Accept-Version)
 * - Environment configuration (sandbox, production, custom base URI)
 * - Authentication headers (Authorization)
 * Use the ElavonApiRequest decorator to add these via fluent interface.
 */
class RetrievePlanListRequest
{
    /**
     * @param string $planListId PlanList ID to retrieve
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     *
     * @throws InvalidArgumentException When plan list ID is empty
     */
    public function __construct(
        private readonly string $planListId,
        private readonly ?RequestFactoryInterface $requestFactory = null,
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
        $requestFactory = $this->requestFactory ?? new Psr17Factory();

        // Build PSR-7 GET request
        return $requestFactory
            ->createRequest('GET', '/plan-lists/' . $this->planListId)
            ->withHeader('Accept', 'application/json');
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
