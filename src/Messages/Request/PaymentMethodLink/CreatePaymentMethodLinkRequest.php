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
 * Create PaymentMethodLink Request.
 *
 * Builds a PSR-7 request for creating a payment method link (POST /payment-method-links).
 *
 * Payment method links allow shoppers to securely provide payment method details
 * using the hosted payment page without creating a transaction.
 */
class CreatePaymentMethodLinkRequest
{
    use HasPsr17Factories;

    private readonly PaymentMethodLink $paymentMethodLink;

    /**
     * @param PaymentMethodLink|array<string, mixed> $paymentMethodLink PaymentMethodLink data or array     *
     * @throws InvalidArgumentException When payment method link data is invalid
     */
    public function __construct(
        PaymentMethodLink|array $paymentMethodLink
    ) {
        // Normalize to PaymentMethodLink object
        $this->paymentMethodLink = match (true) {
            $paymentMethodLink instanceof PaymentMethodLink => $paymentMethodLink,
            is_array($paymentMethodLink) => PaymentMethodLink::fromData($paymentMethodLink),
        };

        // Validate required fields for creation
        $this->validateRequest($this->paymentMethodLink);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Use built-in factories if none provided

        // Serialize payment method link to JSON
        $data = $this->paymentMethodLink->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request
        return $this->getRequestFactory()
            ->createRequest('POST', '/payment-method-links')
            ->withBody($this->getStreamFactory()->createStream($json));
    }

    /**
     * Gets the payment method link being created.
     *
     * @return PaymentMethodLink
     */
    public function getPaymentMethodLink(): PaymentMethodLink
    {
        return $this->paymentMethodLink;
    }

    /**
     * Validates that required fields are present for a payment method link creation request.
     *
     * @param PaymentMethodLink $paymentMethodLink
     * @throws InvalidArgumentException When required fields are missing
     */
    private function validateRequest(PaymentMethodLink $paymentMethodLink): void
    {
        // According to OpenAPI spec, 'expiresAt' and 'shopper' are required for PaymentMethodLinkInput
        if ($paymentMethodLink->expiresAt === null) {
            throw new InvalidArgumentException('ExpiresAt is required for creating a payment method link');
        }

        if ($paymentMethodLink->shopper === null) {
            throw new InvalidArgumentException('Shopper is required for creating a payment method link');
        }
    }
}
