<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\TotalAdjustment;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

class RetrieveTotalAdjustmentListRequest
{
    public function __construct(
        private readonly string $transactionId,
        private readonly array $queryParams = [],
        private readonly ?RequestFactoryInterface $requestFactory = null,
    ) {
        if (empty($this->transactionId)) {
            throw new InvalidArgumentException('Transaction ID cannot be empty');
        }
    }

    public function build(): RequestInterface
    {
        $requestFactory = $this->requestFactory ?? new Psr17Factory();

        $uri = '/transactions/' . $this->transactionId . '/total-adjustments';
        if (!empty($this->queryParams)) {
            $uri .= '?' . http_build_query($this->queryParams);
        }

        return $requestFactory
            ->createRequest('GET', $uri);
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }
}
