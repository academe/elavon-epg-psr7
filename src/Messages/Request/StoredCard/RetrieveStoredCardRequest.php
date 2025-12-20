<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\StoredCard;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Retrieve Stored Card Request.
 *
 * Builds a PSR-7 request for retrieving a single stored card (GET /stored-cards/{id}).
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\RetrieveStoredCardRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 *
 * // Build the base request
 * $request = (new RetrieveStoredCardRequest('sc123'))->build();
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
class RetrieveStoredCardRequest
{
    use HasPsr17Factories;

    /**
     * @param string $storedCardId Stored card ID to retrieve     *
     * @throws InvalidArgumentException When stored card ID is empty
     */
    public function __construct(
        private readonly string $storedCardId
    ) {
        if (empty($this->storedCardId)) {
            throw new InvalidArgumentException('Stored card ID cannot be empty');
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
            ->createRequest('GET', '/stored-cards/' . $this->storedCardId);
    }

    /**
     * Gets the stored card ID being retrieved.
     *
     * @return string
     */
    public function getStoredCardId(): string
    {
        return $this->storedCardId;
    }
}
