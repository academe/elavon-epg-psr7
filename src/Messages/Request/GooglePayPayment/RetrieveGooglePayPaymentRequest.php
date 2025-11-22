<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\GooglePayPayment;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

class RetrieveGooglePayPaymentRequest
{
    public function __construct(
        private readonly string $googlePayPaymentId,
        private readonly ?RequestFactoryInterface $requestFactory = null,
    ) {
        if (empty($this->googlePayPaymentId)) {
            throw new InvalidArgumentException('Google Pay payment ID cannot be empty');
        }
    }

    public function build(): RequestInterface
    {
        $requestFactory = $this->requestFactory ?? new Psr17Factory();

        return $requestFactory
            ->createRequest('GET', '/google-pay-payments/' . $this->googlePayPaymentId);
    }

    public function getGooglePayPaymentId(): string
    {
        return $this->googlePayPaymentId;
    }
}
