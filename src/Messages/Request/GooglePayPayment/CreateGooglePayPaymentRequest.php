<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\GooglePayPayment;

use Academe\Elavon\Epg\Psr7\Dtos\GooglePayPayment;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

class CreateGooglePayPaymentRequest
{
    use HasPsr17Factories;

    public function __construct(
        public readonly GooglePayPayment $googlePayPayment
    ) {
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{googlePayPayment: GooglePayPayment|array<string, mixed>} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('googlePayPayment', $data)) {
            throw new InvalidArgumentException("Missing required key 'googlePayPayment' in data");
        }

        $googlePayPayment = $data['googlePayPayment'] instanceof GooglePayPayment
            ? $data['googlePayPayment']
            : GooglePayPayment::fromData($data['googlePayPayment']);

        return new static($googlePayPayment);
    }

    public function build(): RequestInterface
    {
        $data = $this->googlePayPayment->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        return $this->getRequestFactory()
            ->createRequest('POST', '/google-pay-payments')
            ->withBody($this->getStreamFactory()->createStream($json));
    }
}
