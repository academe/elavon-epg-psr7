<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\StoredAchPayment;

use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Retrieve Stored ACH Payment List Request.
 *
 * Builds a PSR-7 request for retrieving paginated stored ACH payment lists (GET /stored-ach-payments).
 *
 * Supports pagination via query parameters (limit, offset, etc.).
 *
 * Example usage with ElavonApiRequest decorator:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\StoredAchPayment\RetrieveStoredAchPaymentListRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiRequest;
 *
 * // Build the base request with optional query params
 * $request = (new RetrieveStoredAchPaymentListRequest([
 *     'limit' => 50,
 *     'offset' => 100,
 * ]))->build();
 *
 * // Add Elavon API headers, environment, and authentication
 * $elavonRequest = ElavonApiRequest::create($request)
 *     ->withSandbox()
 *     ->withAuthentication($merchantAlias, $apiKey);
 *
 * // Send the request
 * $response = $httpClient->sendRequest($elavonRequest);
 * ```
 *
 * Note: This class builds the base request but does NOT add:
 * - Elavon API headers (Accept, Accept-Version)
 * - Environment configuration (sandbox, production, custom base URI)
 * - Authentication headers (Authorization)
 * Use the ElavonApiRequest decorator to add these via fluent interface.
 */
class RetrieveStoredAchPaymentListRequest
{
    /**
     * @param array<string, mixed> $queryParams Query parameters for pagination/filtering
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     */
    public function __construct(
        private readonly array $queryParams = [],
        private readonly ?RequestFactoryInterface $requestFactory = null,
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
        $requestFactory = $this->requestFactory ?? new Psr17Factory();

        // Build URI with query parameters
        $uri = '/stored-ach-payments';
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
