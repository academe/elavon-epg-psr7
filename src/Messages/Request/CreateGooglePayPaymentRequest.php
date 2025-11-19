<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request;

use Academe\Elavon\Epg\Psr7\Dtos\GooglePayPayment;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

class CreateGooglePayPaymentRequest
{
    private readonly GooglePayPayment $googlePayPayment;

    public function __construct(
        GooglePayPayment|array $googlePayPayment,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
        private readonly string $baseUri = 'https://api.eu.elavonpayments.com',
    ) {
        $this->googlePayPayment = match (true) {
            $googlePayPayment instanceof GooglePayPayment => $googlePayPayment,
            is_array($googlePayPayment) => GooglePayPayment::fromData($googlePayPayment),
        };
    }

    public function build(): RequestInterface
    {
        $requestFactory = $this->requestFactory ?? new Psr17Factory();
        $streamFactory = $this->streamFactory ?? new Psr17Factory();

        $data = $this->googlePayPayment->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        return $requestFactory
            ->createRequest('POST', $this->baseUri . '/google-pay-payments')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withBody($streamFactory->createStream($json));
    }

    public function getGooglePayPayment(): GooglePayPayment
    {
        return $this->googlePayPayment;
    }
}
