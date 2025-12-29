<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Subscription;

use Academe\Elavon\Epg\Psr7\Contracts\RequestMessage;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Retrieve Subscription List Request.
 *
 * Builds a PSR-7 request for retrieving a paginated list of subscriptions (GET /subscriptions).
 *
 * Supports pagination via pageToken and limit parameters.
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\Subscription\RetrieveSubscriptionListRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 *
 * // Build the base request with pagination
 * $request = (new RetrieveSubscriptionListRequest(
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
class RetrieveSubscriptionListRequest implements RequestMessage
{
    use HasPsr17Factories;

    /**
     * @param string|null $pageToken Page token for pagination
     * @param int|null $limit Maximum number of items to return (default varies by endpoint)     *
     * @throws InvalidArgumentException When validation fails
     */
    public function __construct(
        public readonly ?string $pageToken = null,
        public readonly ?int $limit = null
    ) {
        if ($this->limit !== null && $this->limit < 1) {
            throw new InvalidArgumentException('Limit must be at least 1');
        }
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{queryParams?: array<string, mixed>} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        return new static(
            $data['pageToken'] ?? null,
            $data['limit'] ?? null
        );
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Build query string
        $queryParams = [];
        if ($this->pageToken !== null) {
            $queryParams['pageToken'] = $this->pageToken;
        }
        if ($this->limit !== null) {
            $queryParams['limit'] = (string) $this->limit;
        }

        $uri = '/subscriptions';
        if (!empty($queryParams)) {
            $uri .= '?' . http_build_query($queryParams);
        }

        // Build PSR-7 GET request
        return $this->getRequestFactory()
            ->createRequest('GET', $uri);
    }
}
