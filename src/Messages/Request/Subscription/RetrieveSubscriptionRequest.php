<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Subscription;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Retrieve Subscription Request.
 *
 * Builds a PSR-7 request for retrieving a subscription by ID (GET /subscriptions/{id}).
 *
 * Example usage with ElavonApiRequest decorator:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\Subscription\RetrieveSubscriptionRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiRequest;
 *
 * // Build the base request
 * $request = (new RetrieveSubscriptionRequest('6xxFwvM8BqmM6T6DcF3DyTB3'))->build();
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
class RetrieveSubscriptionRequest
{
    /**
     * @param string $subscriptionId Subscription ID to retrieve
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     * @param string $baseUri Base URI for the API (e.g., "https://api.eu.elavonpayments.com")
     *
     * @throws InvalidArgumentException When subscription ID is empty
     */
    public function __construct(
        private readonly string $subscriptionId,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly string $baseUri = 'https://api.eu.elavonpayments.com',
    ) {
        if (empty($this->subscriptionId)) {
            throw new InvalidArgumentException('Subscription ID cannot be empty');
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
            ->createRequest('GET', $this->baseUri . '/subscriptions/' . $this->subscriptionId)
            ->withHeader('Accept', 'application/json');
    }

    /**
     * Gets the subscription ID being retrieved.
     *
     * @return string
     */
    public function getSubscriptionId(): string
    {
        return $this->subscriptionId;
    }
}
