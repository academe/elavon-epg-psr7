<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PaymentMethodLink;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentMethodLink;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Update PaymentMethodLink Request.
 *
 * Builds a PSR-7 request for updating a payment method link (POST /payment-method-links/{id}).
 *
 * Typically used to cancel a payment method link by setting doCancel to true.
 */
class UpdatePaymentMethodLinkRequest
{
    use HasPsr17Factories;

    private readonly PaymentMethodLink $paymentMethodLink;

    /**
     * @param string $paymentMethodLinkId PaymentMethodLink ID to update
     * @param PaymentMethodLink|array<string, mixed> $paymentMethodLink PaymentMethodLink data or array     *
     * @throws InvalidArgumentException When payment method link data is invalid
     */
    public function __construct(
        private readonly string $paymentMethodLinkId,
        PaymentMethodLink|array $paymentMethodLink
    ) {
        if (empty($this->paymentMethodLinkId)) {
            throw new InvalidArgumentException('PaymentMethodLink ID cannot be empty');
        }

        // Normalize to PaymentMethodLink object
        $this->paymentMethodLink = match (true) {
            $paymentMethodLink instanceof PaymentMethodLink => $paymentMethodLink,
            is_array($paymentMethodLink) => PaymentMethodLink::fromData($paymentMethodLink),
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
        $requestFactory = $this->getRequestFactory();
        $streamFactory = $this->getStreamFactory();

        // Serialize payment method link to JSON
        $data = $this->paymentMethodLink->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request (update uses POST, not PUT/PATCH)
        return $requestFactory
            ->createRequest('POST', '/payment-method-links/' . $this->paymentMethodLinkId)
            ->withBody($streamFactory->createStream($json));
    }

    /**
     * Gets the payment method link ID being updated.
     *
     * @return string
     */
    public function getPaymentMethodLinkId(): string
    {
        return $this->paymentMethodLinkId;
    }

    /**
     * Gets the payment method link data.
     *
     * @return PaymentMethodLink
     */
    public function getPaymentMethodLink(): PaymentMethodLink
    {
        return $this->paymentMethodLink;
    }
}
