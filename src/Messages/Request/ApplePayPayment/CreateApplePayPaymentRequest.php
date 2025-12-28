<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\ApplePayPayment;

use Academe\Elavon\Epg\Psr7\Dtos\ApplePayPayment;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

class CreateApplePayPaymentRequest
{
    use HasPsr17Factories;

    public function __construct(
        public readonly ApplePayPayment $applePayPayment
    ) {
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{applePayPayment: ApplePayPayment|array<string, mixed>} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('applePayPayment', $data)) {
            throw new InvalidArgumentException("Missing required key 'applePayPayment' in data");
        }

        $applePayPayment = $data['applePayPayment'] instanceof ApplePayPayment
            ? $data['applePayPayment']
            : ApplePayPayment::fromData($data['applePayPayment']);

        return new static($applePayPayment);
    }

    public function build(): RequestInterface
    {
        $data = $this->applePayPayment->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        return $this->getRequestFactory()
            ->createRequest('POST', '/apple-pay-payments')
            ->withBody($this->getStreamFactory()->createStream($json));
    }
}
