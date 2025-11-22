<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PaymentMethodLink;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Retrieve PaymentMethodLink Request.
 *
 * Builds a PSR-7 request for retrieving a single payment method link (GET /payment-method-links/{id}).
 */
class RetrievePaymentMethodLinkRequest
{
    /**
     * @param string $paymentMethodLinkId PaymentMethodLink ID to retrieve
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     *
     * @throws InvalidArgumentException When payment method link ID is empty
     */
    public function __construct(
        private readonly string $paymentMethodLinkId,
        private readonly ?RequestFactoryInterface $requestFactory = null,
    ) {
        if (empty($this->paymentMethodLinkId)) {
            throw new InvalidArgumentException('PaymentMethodLink ID cannot be empty');
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
            ->createRequest('GET', '/payment-method-links/' . $this->paymentMethodLinkId)
            ->withHeader('Accept', 'application/json');
    }

    /**
     * Gets the payment method link ID being retrieved.
     *
     * @return string
     */
    public function getPaymentMethodLinkId(): string
    {
        return $this->paymentMethodLinkId;
    }
}
