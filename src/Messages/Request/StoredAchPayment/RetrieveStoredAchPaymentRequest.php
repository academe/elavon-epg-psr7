<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\StoredAchPayment;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Retrieve Stored ACH Payment Request.
 *
 * Builds a PSR-7 request for retrieving a specific stored ACH payment (GET /stored-ach-payments/{id}).
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\StoredAchPayment\RetrieveStoredAchPaymentRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 *
 * // Build the base request
 * $request = (new RetrieveStoredAchPaymentRequest('sap123'))->build();
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
class RetrieveStoredAchPaymentRequest
{
    use HasPsr17Factories;

    /**
     * @param string $storedAchPaymentId Stored ACH payment ID to retrieve     *
     * @throws InvalidArgumentException When stored ACH payment ID is empty
     */
    public function __construct(
        private readonly string $storedAchPaymentId
    ) {
        if (empty($this->storedAchPaymentId)) {
            throw new InvalidArgumentException('Stored ACH payment ID cannot be empty');
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

        // Build PSR-7 GET request
        return $this->getRequestFactory()
            ->createRequest('GET', '/stored-ach-payments/' . $this->storedAchPaymentId);
    }

    /**
     * Gets the stored ACH payment ID being retrieved.
     *
     * @return string
     */
    public function getStoredAchPaymentId(): string
    {
        return $this->storedAchPaymentId;
    }
}
