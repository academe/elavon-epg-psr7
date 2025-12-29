<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\ApplePayPayment;

use Academe\Elavon\Epg\Psr7\Contracts\RequestMessage;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

class RetrieveApplePayPaymentRequest implements RequestMessage
{
    use HasPsr17Factories;

    public function __construct(
        public readonly string $applePayPaymentId
    ) {
        if (empty($this->applePayPaymentId)) {
            throw new InvalidArgumentException('Apple Pay payment ID cannot be empty');
        }
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{applePayPaymentId: string} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('applePayPaymentId', $data)) {
            throw new InvalidArgumentException("Missing required key 'applePayPaymentId' in data");
        }

        return new static($data['applePayPaymentId']);
    }

    public function build(): RequestInterface
    {
        return $this->getRequestFactory()
            ->createRequest('GET', '/apple-pay-payments/' . $this->applePayPaymentId);
    }
}
