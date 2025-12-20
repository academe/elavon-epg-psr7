<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\ApplePayPayment;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

class RetrieveApplePayPaymentRequest
{
    use HasPsr17Factories;

    public function __construct(
        private readonly string $applePayPaymentId
    ) {
        if (empty($this->applePayPaymentId)) {
            throw new InvalidArgumentException('Apple Pay payment ID cannot be empty');
        }
    }

    public function build(): RequestInterface
    {
        $requestFactory = $this->getRequestFactory();

        return $requestFactory
            ->createRequest('GET', '/apple-pay-payments/' . $this->applePayPaymentId);
    }

    public function getApplePayPaymentId(): string
    {
        return $this->applePayPaymentId;
    }
}
