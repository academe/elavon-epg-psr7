<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\ApplePayPaymentSession;

use Academe\Elavon\Epg\Psr7\Dtos\ApplePayPaymentSession;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

class CreateApplePayPaymentSessionRequest
{
    private readonly ApplePayPaymentSession $applePayPaymentSession;

    public function __construct(
        ApplePayPaymentSession|array $applePayPaymentSession,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
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
        $requestFactory = $this->requestFactory ?? new Psr17Factory();
        $streamFactory = $this->streamFactory ?? new Psr17Factory();

        $data = $this->applePayPaymentSession->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        return $requestFactory
            ->createRequest('POST', '/apple-pay-payment-sessions')
            ->withBody($streamFactory->createStream($json));
    }

    public function getApplePayPaymentSession(): ApplePayPaymentSession
    {
        return $this->applePayPaymentSession;
    }
}
