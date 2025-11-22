<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PaymentLink;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentLink;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Create PaymentLink Request.
 *
 * Builds a PSR-7 request for creating a payment link (POST /payment-links).
 *
 * Payment links contain the details necessary to create a Transaction via HPP.
 * They allow merchants to share a URL with card holders to collect payment details.
 *
 * Example usage with ElavonApiRequest decorator:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\PaymentLink\CreatePaymentLinkRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiRequest;
 * use Academe\Elavon\Epg\Psr7\Dtos\PaymentLink;
 *
 * // Build the payment link
 * $paymentLink = new PaymentLink(
 *     total: ['amount' => '100.00', 'currencyCode' => 'USD'],
 *     expiresAt: '2025-12-31T23:59:59Z',
 *     returnUrl: 'https://merchant.com/return',
 *     description: 'Payment for Invoice #12345',
 * );
 *
 * // Build the request
 * $request = (new CreatePaymentLinkRequest($paymentLink))->build();
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
class CreatePaymentLinkRequest
{
    private readonly PaymentLink $paymentLink;

    /**
     * @param PaymentLink|array<string, mixed> $paymentLink PaymentLink data or array
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     * @param StreamFactoryInterface|null $streamFactory PSR-17 stream factory (uses built-in if null)
     *
     * @throws InvalidArgumentException When payment link data is invalid
     */
    public function __construct(
        PaymentLink|array $paymentLink,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
    ) {
        // Normalize to PaymentLink object
        $this->paymentLink = match (true) {
            $paymentLink instanceof PaymentLink => $paymentLink,
            is_array($paymentLink) => PaymentLink::fromData($paymentLink),
        };

        // Validate required fields for creation
        $this->validatePaymentLinkRequest($this->paymentLink);
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

        // Serialize payment link to JSON
        $data = $this->paymentLink->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request
        return $requestFactory
            ->createRequest('POST', '/payment-links')
            ->withBody($streamFactory->createStream($json));
    }

    /**
     * Gets the payment link being created.
     *
     * @return PaymentLink
     */
    public function getPaymentLink(): PaymentLink
    {
        return $this->paymentLink;
    }

    /**
     * Validates that required fields are present for a payment link creation request.
     *
     * @param PaymentLink $paymentLink
     * @throws InvalidArgumentException When required fields are missing
     */
    private function validatePaymentLinkRequest(PaymentLink $paymentLink): void
    {
        // According to OpenAPI spec, 'total' and 'expiresAt' are required for PaymentLinkInput
        if ($paymentLink->total === null) {
            throw new InvalidArgumentException('PaymentLink total is required for creating a payment link');
        }

        if ($paymentLink->expiresAt === null) {
            throw new InvalidArgumentException('PaymentLink expiresAt is required for creating a payment link');
        }
    }
}
