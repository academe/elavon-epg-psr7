<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\HsmCard;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

class RetrieveHsmCardRequest
{
    use HasPsr17Factories;

    public function __construct(
        private readonly string $hsmCardId
    ) {
        if (empty($this->hsmCardId)) {
            throw new InvalidArgumentException('HsmCard ID cannot be empty');
        }
    }

    public function build(): RequestInterface
    {
        $requestFactory = $this->getRequestFactory();

        return $requestFactory
            ->createRequest('GET', '/hsm-cards/' . $this->hsmCardId);
    }

    public function getHsmCardId(): string
    {
        return $this->hsmCardId;
    }
}
