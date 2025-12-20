<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\ForexAdvice;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Retrieve Forex Advice Request.
 *
 * Builds a PSR-7 request for retrieving a single forex advice (GET /forex-advices/{id}).
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\RetrieveForexAdviceRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 *
 * // Build the base request
 * $request = (new RetrieveForexAdviceRequest('fxa123'))->build();
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
class RetrieveForexAdviceRequest
{
    use HasPsr17Factories;

    /**
     * @param string $forexAdviceId Forex advice ID to retrieve     *
     * @throws InvalidArgumentException When forex advice ID is empty
     */
    public function __construct(
        private readonly string $forexAdviceId
    ) {
        if (empty($this->forexAdviceId)) {
            throw new InvalidArgumentException('Forex advice ID cannot be empty');
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

        // Build PSR-7 GET request
        return $requestFactory
            ->createRequest('GET', '/forex-advices/' . $this->forexAdviceId);
    }

    /**
     * Gets the forex advice ID being retrieved.
     *
     * @return string
     */
    public function getForexAdviceId(): string
    {
        return $this->forexAdviceId;
    }
}
