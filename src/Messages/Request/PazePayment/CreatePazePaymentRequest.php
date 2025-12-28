<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PazePayment;

use Academe\Elavon\Epg\Psr7\Dtos\PazePayment;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

class CreatePazePaymentRequest
{
    use HasPsr17Factories;

    public function __construct(
        public readonly PazePayment $pazePayment
    ) {
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{pazePayment: PazePayment|array<string, mixed>} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('pazePayment', $data)) {
            throw new InvalidArgumentException("Missing required key 'pazePayment' in data");
        }

        $pazePayment = $data['pazePayment'] instanceof PazePayment
            ? $data['pazePayment']
            : PazePayment::fromData($data['pazePayment']);

        return new static($pazePayment);
    }

    public function build(): RequestInterface
    {
        $data = $this->pazePayment->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        return $this->getRequestFactory()
            ->createRequest('POST', '/paze-payments')
            ->withBody($this->getStreamFactory()->createStream($json));
    }
}
