<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Transaction;

use Academe\Elavon\Epg\Psr7\Dtos\QueryParams;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;
use Psr\Http\Message\RequestInterface;

/**
 * Retrieve Transaction List Request.
 *
 * Builds a PSR-7 request for retrieving paginated transaction lists (GET /transactions).
 *
 * Supports pagination via QueryParams (pageToken, limit).
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Dtos\QueryParams;
 * use Academe\Elavon\Epg\Psr7\Messages\Request\Transaction\RetrieveTransactionListRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 *
 * // Build the base request with pagination
 * $queryParams = QueryParams::create()->withLimit(50);
 * $request = (new RetrieveTransactionListRequest($queryParams))->build();
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
class RetrieveTransactionListRequest
{
    use HasPsr17Factories;

    public function __construct(
        public readonly QueryParams $queryParams = new QueryParams()
    ) {
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{queryParams?: QueryParams|array<string, mixed>} $data
     */
    public static function fromData(array $data): static
    {
        $queryParams = $data['queryParams'] ?? new QueryParams();

        if (is_array($queryParams)) {
            $queryParams = QueryParams::fromArray($queryParams);
        }

        return new static($queryParams);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        $request = $this->getRequestFactory()->createRequest('GET', '/transactions');

        if (! $this->queryParams->isEmpty()) {
            $request = $request->withUri($this->queryParams->apply($request->getUri()));
        }

        return $request;
    }
}
