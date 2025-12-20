<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PaymentLinkEvent;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

class RetrievePaymentLinkEventListRequest
{
    use HasPsr17Factories;

    public function __construct(
        private readonly array $queryParams = []
    ) {
    }

    public function build(): RequestInterface
    {

        $uri = '/payment-link-events';
        if (!empty($this->queryParams)) {
            $uri .= '?' . http_build_query($this->queryParams);
        }

        return $this->getRequestFactory()
            ->createRequest('GET', $uri);
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }
}
