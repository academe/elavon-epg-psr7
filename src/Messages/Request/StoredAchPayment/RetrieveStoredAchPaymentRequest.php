<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\StoredAchPayment;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Retrieve Stored ACH Payment Request.
 *
 * Builds a PSR-7 request for retrieving a specific stored ACH payment (GET /stored-ach-payments/{id}).
 *
 * Example usage with ElavonApiRequest decorator:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\StoredAchPayment\RetrieveStoredAchPaymentRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiRequest;
 *
 * // Build the base request
 * $request = (new RetrieveStoredAchPaymentRequest('sap123'))->build();
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
class RetrieveStoredAchPaymentRequest
{
    /**
     * @param string $storedAchPaymentId Stored ACH payment ID to retrieve
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     *
     * @throws InvalidArgumentException When stored ACH payment ID is empty
     */
    public function __construct(
        private readonly string $storedAchPaymentId,
        private readonly ?RequestFactoryInterface $requestFactory = null,
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
        $requestFactory = $this->requestFactory ?? new Psr17Factory();

        // Build PSR-7 GET request
        return $requestFactory
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
