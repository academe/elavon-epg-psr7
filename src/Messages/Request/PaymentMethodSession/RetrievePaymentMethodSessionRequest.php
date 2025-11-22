<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PaymentMethodSession;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Retrieve PaymentMethodSession Request.
 *
 * Builds a PSR-7 request for retrieving a single payment method session (GET /payment-method-sessions/{id}).
 */
class RetrievePaymentMethodSessionRequest
{
    /**
     * @param string $paymentMethodSessionId PaymentMethodSession ID to retrieve
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     *
     * @throws InvalidArgumentException When payment method session ID is empty
     */
    public function __construct(
        private readonly string $paymentMethodSessionId,
        private readonly ?RequestFactoryInterface $requestFactory = null,
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
        $requestFactory = $this->requestFactory ?? new Psr17Factory();

        // Build PSR-7 GET request
        return $requestFactory
            ->createRequest('GET', '/payment-method-sessions/' . $this->paymentMethodSessionId)
            ->withHeader('Accept', 'application/json');
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
