<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\ApplePayPayment;

use Academe\Elavon\Epg\Psr7\Dtos\ApplePayPayment;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

class CreateApplePayPaymentRequest
{
    use HasPsr17Factories;

    private readonly ApplePayPayment $applePayPayment;

    public function __construct(
        ApplePayPayment|array $applePayPayment
    ) {
        $this->applePayPayment = match (true) {
            $applePayPayment instanceof ApplePayPayment => $applePayPayment,
            is_array($applePayPayment) => ApplePayPayment::fromData($applePayPayment),
        };
    }

    public function build(): RequestInterface
    {

        $data = $this->applePayPayment->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        return $this->getRequestFactory()
            ->createRequest('POST', '/apple-pay-payments')
            ->withBody($this->getStreamFactory()->createStream($json));
    }

    public function getApplePayPayment(): ApplePayPayment
    {
        return $this->applePayPayment;
    }
}
