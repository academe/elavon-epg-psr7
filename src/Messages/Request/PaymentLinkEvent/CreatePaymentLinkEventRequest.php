<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PaymentLinkEvent;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentLinkEvent;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

class CreatePaymentLinkEventRequest
{
    use HasPsr17Factories;

    public function __construct(
        public readonly PaymentLinkEvent $paymentLinkEvent
    ) {
        $this->validateRequest($this->paymentLinkEvent);
    }

    /**
     * @param array{paymentLinkEvent: PaymentLinkEvent|array<string, mixed>} $data
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('paymentLinkEvent', $data)) {
            throw new InvalidArgumentException("Missing required key 'paymentLinkEvent' in data");
        }

        $paymentLinkEvent = $data['paymentLinkEvent'] instanceof PaymentLinkEvent
            ? $data['paymentLinkEvent']
            : PaymentLinkEvent::fromData($data['paymentLinkEvent']);

        return new static($paymentLinkEvent);
    }

    public function build(): RequestInterface
    {
        $data = $this->paymentLinkEvent->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        return $this->getRequestFactory()
            ->createRequest('POST', '/payment-link-events')
            ->withBody($this->getStreamFactory()->createStream($json));
    }

    private function validateRequest(PaymentLinkEvent $event): void
    {
        if ($event->paymentLink === null) {
            throw new InvalidArgumentException('PaymentLink is required');
        }

        if ($event->type === null) {
            throw new InvalidArgumentException('Type is required');
        }

        if ($event->shopperEmailAddress === null) {
            throw new InvalidArgumentException('Shopper email address is required');
        }
    }
}
