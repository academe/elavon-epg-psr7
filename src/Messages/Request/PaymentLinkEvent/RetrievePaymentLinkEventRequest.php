<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PaymentLinkEvent;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

class RetrievePaymentLinkEventRequest
{
    use HasPsr17Factories;

    public function __construct(
        private readonly string $paymentLinkEventId
    ) {
        if (empty($this->paymentLinkEventId)) {
            throw new InvalidArgumentException('PaymentLinkEvent ID cannot be empty');
        }
    }

    public function build(): RequestInterface
    {

        return $this->getRequestFactory()
            ->createRequest('GET', '/payment-link-events/' . $this->paymentLinkEventId);
    }

    public function getPaymentLinkEventId(): string
    {
        return $this->paymentLinkEventId;
    }
}
