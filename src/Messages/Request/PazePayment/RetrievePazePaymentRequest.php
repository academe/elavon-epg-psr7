<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PazePayment;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

class RetrievePazePaymentRequest
{
    public function __construct(
        private readonly string $pazePaymentId,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly string $baseUri = 'https://api.eu.elavonpayments.com',
    ) {
        if (empty($this->pazePaymentId)) {
            throw new InvalidArgumentException('Paze payment ID cannot be empty');
        }
    }

    public function build(): RequestInterface
    {
        $requestFactory = $this->requestFactory ?? new Psr17Factory();

        return $requestFactory
            ->createRequest('GET', $this->baseUri . '/paze-payments/' . $this->pazePaymentId)
            ->withHeader('Accept', 'application/json');
    }

    public function getPazePaymentId(): string
    {
        return $this->pazePaymentId;
    }
}
