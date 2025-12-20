<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\ApplePayPaymentSession;

use Academe\Elavon\Epg\Psr7\Dtos\ApplePayPaymentSession;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

class CreateApplePayPaymentSessionRequest
{
    use HasPsr17Factories;

    private readonly ApplePayPaymentSession $applePayPaymentSession;

    public function __construct(
        ApplePayPaymentSession|array $applePayPaymentSession
    ) {
        $this->applePayPaymentSession = match (true) {
            $applePayPaymentSession instanceof ApplePayPaymentSession => $applePayPaymentSession,
            is_array($applePayPaymentSession) => ApplePayPaymentSession::fromData($applePayPaymentSession),
        };

        if ($this->applePayPaymentSession->initiativeContext === null) {
            throw new InvalidArgumentException('Initiative context is required');
        }
    }

    public function build(): RequestInterface
    {

        $data = $this->applePayPaymentSession->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        return $this->getRequestFactory()
            ->createRequest('POST', '/apple-pay-payment-sessions')
            ->withBody($this->getStreamFactory()->createStream($json));
    }

    public function getApplePayPaymentSession(): ApplePayPaymentSession
    {
        return $this->applePayPaymentSession;
    }
}
