<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PaymentSession;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentSession;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Update PaymentSession Request.
 *
 * Builds a PSR-7 request for updating a payment session (POST /payment-sessions/{id}).
 *
 * Overwrites an existing payment session with new information.
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\PaymentSession\UpdatePaymentSessionRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
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
class UpdatePaymentSessionRequest
{
    use HasPsr17Factories;

    private readonly PaymentSession $paymentSession;

    /**
     * @param string $paymentSessionId PaymentSession ID to update
     * @param PaymentSession|array<string, mixed> $paymentSession PaymentSession data or array     *
     * @throws InvalidArgumentException When payment session data is invalid or ID is empty
     */
    public function __construct(
        private readonly string $paymentSessionId,
        PaymentSession|array $paymentSession
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

        // Serialize payment session to JSON
        $data = $this->paymentSession->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request (update uses POST, not PUT/PATCH)
        return $this->getRequestFactory()
            ->createRequest('POST', '/payment-sessions/' . $this->paymentSessionId)
            ->withBody($this->getStreamFactory()->createStream($json));
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
