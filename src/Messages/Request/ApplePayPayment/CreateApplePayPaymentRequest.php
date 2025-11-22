<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\ApplePayPayment;

use Academe\Elavon\Epg\Psr7\Dtos\ApplePayPayment;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

class CreateApplePayPaymentRequest
{
    private readonly ApplePayPayment $applePayPayment;

    public function __construct(
        ApplePayPayment|array $applePayPayment,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
    ) {
        $this->applePayPayment = match (true) {
            $applePayPayment instanceof ApplePayPayment => $applePayPayment,
            is_array($applePayPayment) => ApplePayPayment::fromData($applePayPayment),
        };
    }

    public function build(): RequestInterface
    {
        $requestFactory = $this->requestFactory ?? new Psr17Factory();
        $streamFactory = $this->streamFactory ?? new Psr17Factory();

        $data = $this->applePayPayment->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        return $requestFactory
            ->createRequest('POST', '/apple-pay-payments')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withBody($streamFactory->createStream($json));
    }

    public function getApplePayPayment(): ApplePayPayment
    {
        return $this->applePayPayment;
    }
}
