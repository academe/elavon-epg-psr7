<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\HsmCard;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

class RetrieveHsmCardRequest
{
    public function __construct(
        private readonly string $hsmCardId,
        private readonly ?RequestFactoryInterface $requestFactory = null,
    ) {
        if (empty($this->hsmCardId)) {
            throw new InvalidArgumentException('HsmCard ID cannot be empty');
        }
    }

    public function build(): RequestInterface
    {
        $requestFactory = $this->requestFactory ?? new Psr17Factory();

        return $requestFactory
            ->createRequest('GET', '/hsm-cards/' . $this->hsmCardId)
            ->withHeader('Accept', 'application/json');
    }

    public function getHsmCardId(): string
    {
        return $this->hsmCardId;
    }
}
