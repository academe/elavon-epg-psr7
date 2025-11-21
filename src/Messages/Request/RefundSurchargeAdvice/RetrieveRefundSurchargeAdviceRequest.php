<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\RefundSurchargeAdvice;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Retrieve Refund Surcharge Advice Request.
 *
 * Builds a PSR-7 request for retrieving a single refund surcharge advice (GET /refund-surcharge-advices/{id}).
 *
 * Example usage with ElavonApiRequest decorator:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\RetrieveRefundSurchargeAdviceRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiRequest;
 *
 * // Build the base request
 * $request = (new RetrieveRefundSurchargeAdviceRequest('rsa123'))->build();
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
class RetrieveRefundSurchargeAdviceRequest
{
    /**
     * @param string $refundSurchargeAdviceId Refund surcharge advice ID to retrieve
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     * @param string $baseUri Base URI for the API (e.g., "https://api.eu.elavonpayments.com")
     *
     * @throws InvalidArgumentException When refund surcharge advice ID is empty
     */
    public function __construct(
        private readonly string $refundSurchargeAdviceId,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly string $baseUri = 'https://api.eu.elavonpayments.com',
    ) {
        if (empty($this->refundSurchargeAdviceId)) {
            throw new InvalidArgumentException('Refund surcharge advice ID cannot be empty');
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
            ->createRequest('GET', $this->baseUri . '/refund-surcharge-advices/' . $this->refundSurchargeAdviceId)
            ->withHeader('Accept', 'application/json');
    }

    /**
     * Gets the refund surcharge advice ID being retrieved.
     *
     * @return string
     */
    public function getRefundSurchargeAdviceId(): string
    {
        return $this->refundSurchargeAdviceId;
    }
}
