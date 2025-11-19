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
 * Create PaymentSession Request.
 *
 * Builds a PSR-7 request for creating a payment session (POST /payment-sessions).
 *
 * Payment sessions securely collect payment details from shoppers using the
 * hosted payment page, minimizing PCI DSS scope for merchants.
 *
 * Example usage with ElavonApiRequest decorator:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\PaymentSession\CreatePaymentSessionRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiRequest;
 * use Academe\Elavon\Epg\Psr7\Dtos\PaymentSession;
 *
 * // Build the payment session
 * $paymentSession = new PaymentSession(
 *     order: 'https://api.eu.convergepay.com/orders/ord123',
 *     returnUrl: 'https://merchant.com/return',
 *     cancelUrl: 'https://merchant.com/cancel',
 *     doCreateTransaction: true,
 * );
 *
 * // Build the request
 * $request = (new CreatePaymentSessionRequest($paymentSession))->build();
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
class CreatePaymentSessionRequest
{
    private readonly PaymentSession $paymentSession;

    /**
     * @param PaymentSession|array<string, mixed> $paymentSession PaymentSession data or array
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     * @param StreamFactoryInterface|null $streamFactory PSR-17 stream factory (uses built-in if null)
     * @param string $baseUri Base URI for the API (e.g., "https://api.eu.elavonpayments.com")
     *
     * @throws InvalidArgumentException When payment session data is invalid
     */
    public function __construct(
        PaymentSession|array $paymentSession,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
        private readonly string $baseUri = 'https://api.eu.elavonpayments.com',
    ) {
        // Normalize to PaymentSession object
        $this->paymentSession = match (true) {
            $paymentSession instanceof PaymentSession => $paymentSession,
            is_array($paymentSession) => PaymentSession::fromData($paymentSession),
        };

        // Validate required fields for creation
        $this->validatePaymentSessionRequest($this->paymentSession);
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

        // Build PSR-7 POST request
        return $requestFactory
            ->createRequest('POST', $this->baseUri . '/payment-sessions')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withBody($streamFactory->createStream($json));
    }

    /**
     * Gets the payment session being created.
     *
     * @return PaymentSession
     */
    public function getPaymentSession(): PaymentSession
    {
        return $this->paymentSession;
    }

    /**
     * Validates that required fields are present for a payment session creation request.
     *
     * @param PaymentSession $paymentSession
     * @throws InvalidArgumentException When required fields are missing
     */
    private function validatePaymentSessionRequest(PaymentSession $paymentSession): void
    {
        // According to OpenAPI spec, 'order' is required for PaymentSessionInput
        if ($paymentSession->order === null) {
            throw new InvalidArgumentException('Order is required for creating a payment session');
        }
    }
}
