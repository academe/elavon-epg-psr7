<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PazePayment;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

class RetrievePazePaymentRequest
{
    use HasPsr17Factories;

    public function __construct(
        public readonly string $pazePaymentId
    ) {
        if (empty($this->pazePaymentId)) {
            throw new InvalidArgumentException('Paze payment ID cannot be empty');
        }
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{pazePaymentId: string} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('pazePaymentId', $data)) {
            throw new InvalidArgumentException("Missing required key 'pazePaymentId' in data");
        }

        return new static($data['pazePaymentId']);
    }

    public function build(): RequestInterface
    {
        return $this->getRequestFactory()
            ->createRequest('GET', '/paze-payments/' . $this->pazePaymentId);
    }
}
