<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PazePayment;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

class RetrievePazePaymentRequest
{
    use HasPsr17Factories;

    public function __construct(
        private readonly string $pazePaymentId
    ) {
        if (empty($this->pazePaymentId)) {
            throw new InvalidArgumentException('Paze payment ID cannot be empty');
        }
    }

    public function build(): RequestInterface
    {
        $requestFactory = $this->getRequestFactory();

        return $requestFactory
            ->createRequest('GET', '/paze-payments/' . $this->pazePaymentId);
    }

    public function getPazePaymentId(): string
    {
        return $this->pazePaymentId;
    }
}
