<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PaymentMethodLink;

use Academe\Elavon\Epg\Psr7\Contracts\RequestMessage;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Retrieve PaymentMethodLink Request.
 *
 * Builds a PSR-7 request for retrieving a single payment method link (GET /payment-method-links/{id}).
 */
class RetrievePaymentMethodLinkRequest implements RequestMessage
{
    use HasPsr17Factories;

    /**
     * @param string $paymentMethodLinkId PaymentMethodLink ID to retrieve     *
     * @throws InvalidArgumentException When payment method link ID is empty
     */
    public function __construct(
        public readonly string $paymentMethodLinkId
    ) {
        if (empty($this->paymentMethodLinkId)) {
            throw new InvalidArgumentException('PaymentMethodLink ID cannot be empty');
        }
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{paymentMethodLinkId: string} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('paymentMethodLinkId', $data)) {
            throw new InvalidArgumentException("Missing required key 'paymentMethodLinkId' in data");
        }

        return new static($data['paymentMethodLinkId']);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Build PSR-7 GET request
        return $this->getRequestFactory()
            ->createRequest('GET', '/payment-method-links/' . $this->paymentMethodLinkId);
    }
}
