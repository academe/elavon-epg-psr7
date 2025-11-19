<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PaymentLinkEvent;

use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

class RetrievePaymentLinkEventListRequest
{
    public function __construct(
        private readonly array $queryParams = [],
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly string $baseUri = 'https://api.eu.elavonpayments.com',
    ) {
    }

    public function build(): RequestInterface
    {
        $requestFactory = $this->requestFactory ?? new Psr17Factory();

        $uri = $this->baseUri . '/payment-link-events';
        if (!empty($this->queryParams)) {
            $uri .= '?' . http_build_query($this->queryParams);
        }

        return $requestFactory
            ->createRequest('GET', $uri)
            ->withHeader('Accept', 'application/json');
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }
}
