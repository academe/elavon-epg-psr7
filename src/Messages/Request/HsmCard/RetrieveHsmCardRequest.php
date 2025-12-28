<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\HsmCard;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

class RetrieveHsmCardRequest
{
    use HasPsr17Factories;

    public function __construct(
        public readonly string $hsmCardId
    ) {
        if (empty($this->hsmCardId)) {
            throw new InvalidArgumentException('HsmCard ID cannot be empty');
        }
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{hsmCardId: string} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('hsmCardId', $data)) {
            throw new InvalidArgumentException("Missing required key 'hsmCardId' in data");
        }

        return new static($data['hsmCardId']);
    }

    public function build(): RequestInterface
    {
        return $this->getRequestFactory()
            ->createRequest('GET', '/hsm-cards/' . $this->hsmCardId);
    }
}
