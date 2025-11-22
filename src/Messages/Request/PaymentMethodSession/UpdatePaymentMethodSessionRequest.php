<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PaymentMethodSession;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentMethodSession;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Update PaymentMethodSession Request.
 *
 * Builds a PSR-7 request for updating a payment method session (POST /payment-method-sessions/{id}).
 */
class UpdatePaymentMethodSessionRequest
{
    private readonly PaymentMethodSession $paymentMethodSession;

    /**
     * @param string $paymentMethodSessionId PaymentMethodSession ID to update
     * @param PaymentMethodSession|array<string, mixed> $paymentMethodSession PaymentMethodSession data or array
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     * @param StreamFactoryInterface|null $streamFactory PSR-17 stream factory (uses built-in if null)
     *
     * @throws InvalidArgumentException When payment method session data is invalid
     */
    public function __construct(
        private readonly string $paymentMethodSessionId,
        PaymentMethodSession|array $paymentMethodSession,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
    ) {
        if (empty($this->paymentMethodSessionId)) {
            throw new InvalidArgumentException('PaymentMethodSession ID cannot be empty');
        }

        // Normalize to PaymentMethodSession object
        $this->paymentMethodSession = match (true) {
            $paymentMethodSession instanceof PaymentMethodSession => $paymentMethodSession,
            is_array($paymentMethodSession) => PaymentMethodSession::fromData($paymentMethodSession),
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

        // Serialize payment method session to JSON
        $data = $this->paymentMethodSession->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request (update uses POST, not PUT/PATCH)
        return $requestFactory
            ->createRequest('POST', '/payment-method-sessions/' . $this->paymentMethodSessionId)
            ->withBody($streamFactory->createStream($json));
    }

    /**
     * Gets the payment method session ID being updated.
     *
     * @return string
     */
    public function getPaymentMethodSessionId(): string
    {
        return $this->paymentMethodSessionId;
    }

    /**
     * Gets the payment method session data.
     *
     * @return PaymentMethodSession
     */
    public function getPaymentMethodSession(): PaymentMethodSession
    {
        return $this->paymentMethodSession;
    }
}
