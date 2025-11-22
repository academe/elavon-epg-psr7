<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PaymentLinkEvent;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentLinkEvent;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

class CreatePaymentLinkEventRequest
{
    private readonly PaymentLinkEvent $paymentLinkEvent;

    public function __construct(
        PaymentLinkEvent|array $paymentLinkEvent,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
    ) {
        $this->paymentLinkEvent = match (true) {
            $paymentLinkEvent instanceof PaymentLinkEvent => $paymentLinkEvent,
            is_array($paymentLinkEvent) => PaymentLinkEvent::fromData($paymentLinkEvent),
        };

        $this->validateRequest($this->paymentLinkEvent);
    }

    public function build(): RequestInterface
    {
        $requestFactory = $this->requestFactory ?? new Psr17Factory();
        $streamFactory = $this->streamFactory ?? new Psr17Factory();

        $data = $this->paymentLinkEvent->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        return $requestFactory
            ->createRequest('POST', '/payment-link-events')
            ->withBody($streamFactory->createStream($json));
    }

    public function getPaymentLinkEvent(): PaymentLinkEvent
    {
        return $this->paymentLinkEvent;
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
