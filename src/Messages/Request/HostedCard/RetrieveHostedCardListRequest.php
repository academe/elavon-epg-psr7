<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\HostedCard;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Retrieve Hosted Card List Request.
 *
 * Builds a PSR-7 request for retrieving paginated hosted card lists (GET /hosted-cards).
 *
 * Supports pagination via query parameters (page, limit, etc.).
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\RetrieveHostedCardListRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 *
 * // Build the base request with optional query params
 * $request = (new RetrieveHostedCardListRequest([
 *     'limit' => 50,
 *     'offset' => 100,
 * ]))->build();
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
class RetrieveHostedCardListRequest
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
        $requestFactory = $this->getRequestFactory();

        // Build URI with query parameters
        $uri = '/hosted-cards';
        if (!empty($this->queryParams)) {
            $uri .= '?' . http_build_query($this->queryParams);
        }

        // Build PSR-7 GET request
        return $requestFactory
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
