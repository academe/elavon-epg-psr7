<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PaymentSession;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentSession;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Create PaymentSession Request.
 *
 * Builds a PSR-7 request for creating a payment session (POST /payment-sessions).
 *
 * Payment sessions securely collect payment details from shoppers using the
 * hosted payment page, minimizing PCI DSS scope for merchants.
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\PaymentSession\CreatePaymentSessionRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
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
class CreatePaymentSessionRequest
{
    use HasPsr17Factories;

    /**
     * @param PaymentSession $paymentSession PaymentSession data     *
     * @throws InvalidArgumentException When payment session data is invalid
     */
    public function __construct(
        public readonly PaymentSession $paymentSession
    ) {
        // Validate required fields for creation
        $this->validatePaymentSessionRequest($this->paymentSession);
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{paymentSession: PaymentSession|array<string, mixed>} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('paymentSession', $data)) {
            throw new InvalidArgumentException("Missing required key 'paymentSession' in data");
        }

        $paymentSession = $data['paymentSession'] instanceof PaymentSession
            ? $data['paymentSession']
            : PaymentSession::fromData($data['paymentSession']);

        return new static($paymentSession);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Serialize payment session to JSON
        $data = $this->paymentSession->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request
        return $this->getRequestFactory()
            ->createRequest('POST', '/payment-sessions')
            ->withBody($this->getStreamFactory()->createStream($json));
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
