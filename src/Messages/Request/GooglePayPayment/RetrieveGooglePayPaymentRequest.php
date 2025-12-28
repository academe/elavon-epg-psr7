<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\GooglePayPayment;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

class RetrieveGooglePayPaymentRequest
{
    use HasPsr17Factories;

    public function __construct(
        public readonly string $googlePayPaymentId
    ) {
        if (empty($this->googlePayPaymentId)) {
            throw new InvalidArgumentException('Google Pay payment ID cannot be empty');
        }
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{googlePayPaymentId: string} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('googlePayPaymentId', $data)) {
            throw new InvalidArgumentException("Missing required key 'googlePayPaymentId' in data");
        }

        return new static($data['googlePayPaymentId']);
    }

    public function build(): RequestInterface
    {
        return $this->getRequestFactory()
            ->createRequest('GET', '/google-pay-payments/' . $this->googlePayPaymentId);
    }
}
