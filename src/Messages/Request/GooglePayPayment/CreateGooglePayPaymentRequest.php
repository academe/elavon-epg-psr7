<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\GooglePayPayment;

use Academe\Elavon\Epg\Psr7\Dtos\GooglePayPayment;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

class CreateGooglePayPaymentRequest
{
    use HasPsr17Factories;

    private readonly GooglePayPayment $googlePayPayment;

    public function __construct(
        GooglePayPayment|array $googlePayPayment
    ) {
        $this->googlePayPayment = match (true) {
            $googlePayPayment instanceof GooglePayPayment => $googlePayPayment,
            is_array($googlePayPayment) => GooglePayPayment::fromData($googlePayPayment),
        };
    }

    public function build(): RequestInterface
    {

        $data = $this->googlePayPayment->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        return $this->getRequestFactory()
            ->createRequest('POST', '/google-pay-payments')
            ->withBody($this->getStreamFactory()->createStream($json));
    }

    public function getGooglePayPayment(): GooglePayPayment
    {
        return $this->googlePayPayment;
    }
}
