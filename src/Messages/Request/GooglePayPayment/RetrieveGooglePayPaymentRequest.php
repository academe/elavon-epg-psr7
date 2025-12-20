<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\GooglePayPayment;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

class RetrieveGooglePayPaymentRequest
{
    use HasPsr17Factories;

    public function __construct(
        private readonly string $googlePayPaymentId
    ) {
        if (empty($this->googlePayPaymentId)) {
            throw new InvalidArgumentException('Google Pay payment ID cannot be empty');
        }
    }

    public function build(): RequestInterface
    {

        return $this->getRequestFactory()
            ->createRequest('GET', '/google-pay-payments/' . $this->googlePayPaymentId);
    }

    public function getGooglePayPaymentId(): string
    {
        return $this->googlePayPaymentId;
    }
}
