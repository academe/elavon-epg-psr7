<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PaymentLink;

use Academe\Elavon\Epg\Psr7\Contracts\RequestMessage;
use Academe\Elavon\Epg\Psr7\Dtos\PaymentLink;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Create PaymentLink Request.
 *
 * Builds a PSR-7 request for creating a payment link (POST /payment-links).
 *
 * Payment links contain the details necessary to create a Transaction via HPP.
 * They allow merchants to share a URL with card holders to collect payment details.
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\PaymentLink\CreatePaymentLinkRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
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
class CreatePaymentLinkRequest implements RequestMessage
{
    use HasPsr17Factories;

    /**
     * @param PaymentLink $paymentLink PaymentLink data     *
     * @throws InvalidArgumentException When payment link data is invalid
     */
    public function __construct(
        public readonly PaymentLink $paymentLink
    ) {
        // Validate required fields for creation
        $this->validatePaymentLinkRequest($this->paymentLink);
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{paymentLink: PaymentLink|array<string, mixed>} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('paymentLink', $data)) {
            throw new InvalidArgumentException("Missing required key 'paymentLink' in data");
        }

        $paymentLink = $data['paymentLink'] instanceof PaymentLink
            ? $data['paymentLink']
            : PaymentLink::fromData($data['paymentLink']);

        return new static($paymentLink);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Serialize payment link to JSON
        $data = $this->paymentLink->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request
        return $this->getRequestFactory()
            ->createRequest('POST', '/payment-links')
            ->withBody($this->getStreamFactory()->createStream($json));
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
