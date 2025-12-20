<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Plan;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Retrieve Plan List Request.
 *
 * Builds a PSR-7 request for retrieving a paginated list of plans (GET /plans).
 *
 * Supports pagination via pageToken and limit parameters.
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\Plan\RetrievePlanListRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 *
 * // Build the base request with pagination
 * $request = (new RetrievePlanListRequest(
 *     pageToken: 'abc123',
 *     limit: 50
 * ))->build();
 *
 * // Add Elavon API headers, environment, and authentication
 * $factory = ElavonApiFactory::configure()
 *     ->withRegion('eu')
 *     ->withEnvironment('sandbox')
 *     ->withAuthentication($merchantAlias, $apiKey);
 *
 * // Send the request
 * $apiRequest = $factory->apply($request);
 * $response = $httpClient->sendRequest($apiRequest);
 * ```
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
     * @param string|null $pageToken Page token for pagination
     * @param int|null $limit Maximum number of items to return (default varies by endpoint)     *
     * @throws InvalidArgumentException When validation fails
     */
    public function __construct(
        private readonly ?string $pageToken = null,
        private readonly ?int $limit = null
    ) {
        if ($this->limit !== null && $this->limit < 1) {
            throw new InvalidArgumentException('Limit must be at least 1');
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
        $requestFactory = $this->getRequestFactory();

        // Build query string
        $queryParams = [];
        if ($this->pageToken !== null) {
            $queryParams['pageToken'] = $this->pageToken;
        }
        if ($this->limit !== null) {
            $queryParams['limit'] = (string) $this->limit;
        }

        $uri = '/plans';
        if (!empty($queryParams)) {
            $uri .= '?' . http_build_query($queryParams);
        }

        // Build PSR-7 GET request
        return $requestFactory
            ->createRequest('GET', $uri);
    }

    /**
     * Gets the page token for pagination.
     *
     * @return string|null
     */
    public function getPageToken(): ?string
    {
        return $this->pageToken;
    }

    /**
     * Gets the limit for pagination.
     *
     * @return int|null
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }
}
