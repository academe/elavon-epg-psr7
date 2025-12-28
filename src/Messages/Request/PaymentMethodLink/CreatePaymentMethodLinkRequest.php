<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PaymentMethodLink;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentMethodLink;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
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

    /**
     * @param PaymentMethodLink $paymentMethodLink PaymentMethodLink data     *
     * @throws InvalidArgumentException When payment method link data is invalid
     */
    public function __construct(
        public readonly PaymentMethodLink $paymentMethodLink
    ) {
        // Validate required fields for creation
        $this->validateRequest($this->paymentMethodLink);
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{paymentMethodLink: PaymentMethodLink|array<string, mixed>} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('paymentMethodLink', $data)) {
            throw new InvalidArgumentException("Missing required key 'paymentMethodLink' in data");
        }

        $paymentMethodLink = $data['paymentMethodLink'] instanceof PaymentMethodLink
            ? $data['paymentMethodLink']
            : PaymentMethodLink::fromData($data['paymentMethodLink']);

        return new static($paymentMethodLink);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Serialize payment method link to JSON
        $data = $this->paymentMethodLink->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request
        return $this->getRequestFactory()
            ->createRequest('POST', '/payment-method-links')
            ->withBody($this->getStreamFactory()->createStream($json));
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
