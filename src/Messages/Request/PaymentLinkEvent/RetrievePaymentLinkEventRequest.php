<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PaymentLinkEvent;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

class RetrievePaymentLinkEventRequest
{
    public function __construct(
        private readonly string $paymentLinkEventId,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly string $baseUri = 'https://api.eu.elavonpayments.com',
    ) {
        if (empty($this->paymentLinkEventId)) {
            throw new InvalidArgumentException('PaymentLinkEvent ID cannot be empty');
        }
    }

    public function build(): RequestInterface
    {
        $requestFactory = $this->requestFactory ?? new Psr17Factory();

        return $requestFactory
            ->createRequest('GET', $this->baseUri . '/payment-link-events/' . $this->paymentLinkEventId)
            ->withHeader('Accept', 'application/json');
    }

    public function getPaymentLinkEventId(): string
    {
        return $this->paymentLinkEventId;
    }
}
