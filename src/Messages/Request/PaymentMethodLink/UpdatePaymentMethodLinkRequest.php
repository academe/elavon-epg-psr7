<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PaymentMethodLink;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentMethodLink;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
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

    /**
     * @param string $paymentMethodLinkId PaymentMethodLink ID to update
     * @param PaymentMethodLink $paymentMethodLink PaymentMethodLink data     *
     * @throws InvalidArgumentException When payment method link ID is empty
     */
    public function __construct(
        public readonly string $paymentMethodLinkId,
        public readonly PaymentMethodLink $paymentMethodLink
    ) {
        if (empty($this->paymentMethodLinkId)) {
            throw new InvalidArgumentException('PaymentMethodLink ID cannot be empty');
        }
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{paymentMethodLinkId: string, paymentMethodLink: PaymentMethodLink|array<string, mixed>} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('paymentMethodLinkId', $data)) {
            throw new InvalidArgumentException("Missing required key 'paymentMethodLinkId' in data");
        }

        if (! array_key_exists('paymentMethodLink', $data)) {
            throw new InvalidArgumentException("Missing required key 'paymentMethodLink' in data");
        }

        $paymentMethodLink = $data['paymentMethodLink'] instanceof PaymentMethodLink
            ? $data['paymentMethodLink']
            : PaymentMethodLink::fromData($data['paymentMethodLink']);

        return new static($data['paymentMethodLinkId'], $paymentMethodLink);
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

        // Build PSR-7 POST request (update uses POST, not PUT/PATCH)
        return $this->getRequestFactory()
            ->createRequest('POST', '/payment-method-links/' . $this->paymentMethodLinkId)
            ->withBody($this->getStreamFactory()->createStream($json));
    }
}
