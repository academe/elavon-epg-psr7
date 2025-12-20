<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PaymentMethodLink;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Retrieve PaymentMethodLink Request.
 *
 * Builds a PSR-7 request for retrieving a single payment method link (GET /payment-method-links/{id}).
 */
class RetrievePaymentMethodLinkRequest
{
    use HasPsr17Factories;

    /**
     * @param string $paymentMethodLinkId PaymentMethodLink ID to retrieve     *
     * @throws InvalidArgumentException When payment method link ID is empty
     */
    public function __construct(
        private readonly string $paymentMethodLinkId
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

        // Build PSR-7 GET request
        return $this->getRequestFactory()
            ->createRequest('GET', '/payment-method-links/' . $this->paymentMethodLinkId);
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
