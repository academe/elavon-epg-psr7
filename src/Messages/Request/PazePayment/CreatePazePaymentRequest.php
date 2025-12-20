<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PazePayment;

use Academe\Elavon\Epg\Psr7\Dtos\PazePayment;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

class CreatePazePaymentRequest
{
    use HasPsr17Factories;

    private readonly PazePayment $pazePayment;

    public function __construct(
        PazePayment|array $pazePayment
    ) {
        $this->pazePayment = match (true) {
            $pazePayment instanceof PazePayment => $pazePayment,
            is_array($pazePayment) => PazePayment::fromData($pazePayment),
        };
    }

    public function build(): RequestInterface
    {
        $requestFactory = $this->getRequestFactory();
        $streamFactory = $this->getStreamFactory();

        $data = $this->pazePayment->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        return $requestFactory
            ->createRequest('POST', '/paze-payments')
            ->withBody($streamFactory->createStream($json));
    }

    public function getPazePayment(): PazePayment
    {
        return $this->pazePayment;
    }
}
