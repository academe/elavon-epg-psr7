<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PlanList;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Retrieve PlanList List Request.
 *
 * Builds a PSR-7 request for retrieving paginated plan list lists (GET /plan-lists).
 *
 * Supports pagination via query parameters (pageToken, limit, etc.).
 *
 * Note: This class builds the base request but does NOT add:
 * - Elavon API headers (Accept, Accept-Version)
 * - Environment configuration (sandbox, production, custom base URI)
 * - Authentication headers (Authorization)
 * Use the ElavonApiFactory to add these.
 */
class RetrievePlanListListRequest
{
    use HasPsr17Factories;

    /**
     * @param array<string, mixed> $queryParams Query parameters for pagination/filtering     */
    public function __construct(
        private readonly array $queryParams = []
    ) {
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Use built-in factory if none provided

        // Build URI with query parameters
        $uri = '/plan-lists';
        if (!empty($this->queryParams)) {
            $uri .= '?' . http_build_query($this->queryParams);
        }

        // Build PSR-7 GET request
        return $this->getRequestFactory()
            ->createRequest('GET', $uri);
    }

    /**
     * Gets the query parameters.
     *
     * @return array<string, mixed>
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }
}
