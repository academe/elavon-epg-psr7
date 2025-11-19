<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

class RetrieveApplePayPaymentRequest
{
    public function __construct(
        private readonly string $applePayPaymentId,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly string $baseUri = 'https://api.eu.elavonpayments.com',
    ) {
        if (empty($this->applePayPaymentId)) {
            throw new InvalidArgumentException('ApplePay payment ID cannot be empty');
        }
    }

    public function build(): RequestInterface
    {
        $requestFactory = $this->requestFactory ?? new Psr17Factory();

        return $requestFactory
            ->createRequest('GET', $this->baseUri . '/apple-pay-payments/' . $this->applePayPaymentId)
            ->withHeader('Accept', 'application/json');
    }

    public function getApplePayPaymentId(): string
    {
        return $this->applePayPaymentId;
    }
}
