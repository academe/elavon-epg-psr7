<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Shopper;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

class RetrieveShopperStoredCardsRequest
{
    public function __construct(
        private readonly string $shopperId,
        private readonly array $queryParams = [],
        private readonly ?RequestFactoryInterface $requestFactory = null,
    ) {
        if (empty($this->shopperId)) {
            throw new InvalidArgumentException('Shopper ID cannot be empty');
        }
    }

    public function build(): RequestInterface
    {
        $requestFactory = $this->requestFactory ?? new Psr17Factory();

        $uri = '/shoppers/' . $this->shopperId . '/stored-cards';
        if (!empty($this->queryParams)) {
            $uri .= '?' . http_build_query($this->queryParams);
        }

        return $requestFactory
            ->createRequest('GET', $uri);
    }

    public function getShopperId(): string
    {
        return $this->shopperId;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }
}
