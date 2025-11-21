<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PaymentSession;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentSession;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Update PaymentSession Request.
 *
 * Builds a PSR-7 request for updating a payment session (POST /payment-sessions/{id}).
 *
 * Overwrites an existing payment session with new information.
 *
 * Example usage with ElavonApiRequest decorator:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\PaymentSession\UpdatePaymentSessionRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiRequest;
 * use Academe\Elavon\Epg\Psr7\Dtos\PaymentSession;
 *
 * // Build the updated payment session data
 * $paymentSession = new PaymentSession(
 *     doReset: true,
 *     customReference: 'UPDATED-REF-123',
 * );
 *
 * // Build the request
 * $request = (new UpdatePaymentSessionRequest('ps123', $paymentSession))->build();
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
class UpdatePaymentSessionRequest
{
    private readonly PaymentSession $paymentSession;

    /**
     * @param string $paymentSessionId PaymentSession ID to update
     * @param PaymentSession|array<string, mixed> $paymentSession PaymentSession data or array
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     * @param StreamFactoryInterface|null $streamFactory PSR-17 stream factory (uses built-in if null)
     * @param string $baseUri Base URI for the API (e.g., "https://api.eu.elavonpayments.com")
     *
     * @throws InvalidArgumentException When payment session data is invalid or ID is empty
     */
    public function __construct(
        private readonly string $paymentSessionId,
        PaymentSession|array $paymentSession,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
        private readonly string $baseUri = 'https://api.eu.elavonpayments.com',
    ) {
        if (empty($this->paymentSessionId)) {
            throw new InvalidArgumentException('PaymentSession ID cannot be empty');
        }

        // Normalize to PaymentSession object
        $this->paymentSession = match (true) {
            $paymentSession instanceof PaymentSession => $paymentSession,
            is_array($paymentSession) => PaymentSession::fromData($paymentSession),
        };
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Use built-in factories if none provided
        $requestFactory = $this->requestFactory ?? new Psr17Factory();
        $streamFactory = $this->streamFactory ?? new Psr17Factory();

        // Serialize payment session to JSON
        $data = $this->paymentSession->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request (update uses POST, not PUT/PATCH)
        return $requestFactory
            ->createRequest('POST', $this->baseUri . '/payment-sessions/' . $this->paymentSessionId)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withBody($streamFactory->createStream($json));
    }

    /**
     * Gets the payment session ID being updated.
     *
     * @return string
     */
    public function getPaymentSessionId(): string
    {
        return $this->paymentSessionId;
    }

    /**
     * Gets the payment session data.
     *
     * @return PaymentSession
     */
    public function getPaymentSession(): PaymentSession
    {
        return $this->paymentSession;
    }
}
