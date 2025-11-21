<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PaymentLink;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Retrieve PaymentLink Request.
 *
 * Builds a PSR-7 request for retrieving a single payment link (GET /payment-links/{id}).
 *
 * Example usage with ElavonApiRequest decorator:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\PaymentLink\RetrievePaymentLinkRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiRequest;
 *
 * // Build the base request
 * $request = (new RetrievePaymentLinkRequest('6xxFwvM8BqmM6T6DcF3DyTB3'))->build();
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
class RetrievePaymentLinkRequest
{
    /**
     * @param string $paymentLinkId PaymentLink Resource ID
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     * @param string $baseUri Base URI for the API (e.g., "https://api.eu.elavonpayments.com")
     *
     * @throws InvalidArgumentException When payment link ID is empty
     */
    public function __construct(
        private readonly string $paymentLinkId,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly string $baseUri = 'https://api.eu.elavonpayments.com',
    ) {
        if (empty($this->paymentLinkId)) {
            throw new InvalidArgumentException('PaymentLink ID cannot be empty');
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
            ->createRequest('GET', $this->baseUri . '/payment-links/' . $this->paymentLinkId)
            ->withHeader('Accept', 'application/json');
    }

    /**
     * Gets the payment link ID.
     *
     * @return string
     */
    public function getPaymentLinkId(): string
    {
        return $this->paymentLinkId;
    }
}
