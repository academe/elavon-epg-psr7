<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PaymentLink;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Retrieve PaymentLink Event List Request.
 *
 * Builds a PSR-7 request for retrieving paginated payment link event lists
 * (GET /payment-links/{id}/payment-link-events).
 *
 * Events include actions like making a payment or sending an email reminder.
 *
 * Example usage with ElavonApiRequest decorator:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\PaymentLink\RetrievePaymentLinkEventListRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiRequest;
 *
 * // Build the base request with optional query params
 * $request = (new RetrievePaymentLinkEventListRequest('6xxFwvM8BqmM6T6DcF3DyTB3', [
 *     'limit' => 50,
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
class RetrievePaymentLinkEventListRequest
{
    /**
     * @param string $paymentLinkId PaymentLink Resource ID
     * @param array<string, mixed> $queryParams Query parameters for pagination/filtering
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     * @param string $baseUri Base URI for the API (e.g., "https://api.eu.elavonpayments.com")
     *
     * @throws InvalidArgumentException When payment link ID is empty
     */
    public function __construct(
        private readonly string $paymentLinkId,
        private readonly array $queryParams = [],
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

        // Build URI with query parameters
        $uri = $this->baseUri . '/payment-links/' . $this->paymentLinkId . '/payment-link-events';
        if (!empty($this->queryParams)) {
            $uri .= '?' . http_build_query($this->queryParams);
        }

        // Build PSR-7 GET request
        return $requestFactory
            ->createRequest('GET', $uri)
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
