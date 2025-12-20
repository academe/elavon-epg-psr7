<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PaymentMethodSession;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Retrieve PaymentMethodSession Request.
 *
 * Builds a PSR-7 request for retrieving a single payment method session (GET /payment-method-sessions/{id}).
 */
class RetrievePaymentMethodSessionRequest
{
    use HasPsr17Factories;

    /**
     * @param string $paymentMethodSessionId PaymentMethodSession ID to retrieve     *
     * @throws InvalidArgumentException When payment method session ID is empty
     */
    public function __construct(
        private readonly string $paymentMethodSessionId
    ) {
        if (empty($this->paymentMethodSessionId)) {
            throw new InvalidArgumentException('PaymentMethodSession ID cannot be empty');
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

        // Build PSR-7 GET request
        return $this->getRequestFactory()
            ->createRequest('GET', '/payment-method-sessions/' . $this->paymentMethodSessionId);
    }

    /**
     * Gets the payment method session ID being retrieved.
     *
     * @return string
     */
    public function getPaymentMethodSessionId(): string
    {
        return $this->paymentMethodSessionId;
    }
}
