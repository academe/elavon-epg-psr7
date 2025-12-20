<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PaymentMethodSession;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentMethodSession;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Create PaymentMethodSession Request.
 *
 * Builds a PSR-7 request for creating a payment method session (POST /payment-method-sessions).
 */
class CreatePaymentMethodSessionRequest
{
    use HasPsr17Factories;

    private readonly PaymentMethodSession $paymentMethodSession;

    /**
     * @param PaymentMethodSession|array<string, mixed> $paymentMethodSession PaymentMethodSession data or array     *
     * @throws InvalidArgumentException When payment method session data is invalid
     */
    public function __construct(
        PaymentMethodSession|array $paymentMethodSession
    ) {
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

        // Serialize payment method session to JSON
        $data = $this->paymentMethodSession->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request
        return $this->getRequestFactory()
            ->createRequest('POST', '/payment-method-sessions')
            ->withBody($this->getStreamFactory()->createStream($json));
    }

    /**
     * Gets the payment method session being created.
     *
     * @return PaymentMethodSession
     */
    public function getPaymentMethodSession(): PaymentMethodSession
    {
        return $this->paymentMethodSession;
    }
}
