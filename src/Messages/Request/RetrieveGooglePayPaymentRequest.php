<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

class RetrieveGooglePayPaymentRequest
{
    public function __construct(
        private readonly string $googlePayPaymentId,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly string $baseUri = 'https://api.eu.elavonpayments.com',
    ) {
        if (empty($this->googlePayPaymentId)) {
            throw new InvalidArgumentException('GooglePay payment ID cannot be empty');
        }
    }

    public function build(): RequestInterface
    {
        $requestFactory = $this->requestFactory ?? new Psr17Factory();

        return $requestFactory
            ->createRequest('GET', $this->baseUri . '/google-pay-payments/' . $this->googlePayPaymentId)
            ->withHeader('Accept', 'application/json');
    }

    public function getGooglePayPaymentId(): string
    {
        return $this->googlePayPaymentId;
    }
}
