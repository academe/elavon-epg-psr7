<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PaymentLinkEvent;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

class RetrievePaymentLinkEventRequest
{
    use HasPsr17Factories;

    public function __construct(
        public readonly string $paymentLinkEventId
    ) {
        if (empty($this->paymentLinkEventId)) {
            throw new InvalidArgumentException('PaymentLinkEvent ID cannot be empty');
        }
    }

    /**
     * @param array{paymentLinkEventId: string} $data
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('paymentLinkEventId', $data)) {
            throw new InvalidArgumentException("Missing required key 'paymentLinkEventId' in data");
        }

        return new static($data['paymentLinkEventId']);
    }

    public function build(): RequestInterface
    {
        return $this->getRequestFactory()
            ->createRequest('GET', '/payment-link-events/' . $this->paymentLinkEventId);
    }
}
