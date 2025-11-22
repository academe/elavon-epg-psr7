<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\ApplePayPayment;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

class RetrieveApplePayPaymentRequest
{
    public function __construct(
        private readonly string $applePayPaymentId,
        private readonly ?RequestFactoryInterface $requestFactory = null,
    ) {
        if (empty($this->applePayPaymentId)) {
            throw new InvalidArgumentException('Apple Pay payment ID cannot be empty');
        }
    }

    public function build(): RequestInterface
    {
        $requestFactory = $this->requestFactory ?? new Psr17Factory();

        return $requestFactory
            ->createRequest('GET', '/apple-pay-payments/' . $this->applePayPaymentId);
    }

    public function getApplePayPaymentId(): string
    {
        return $this->applePayPaymentId;
    }
}
