<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Transaction;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

class SendEmailReceiptRequest
{
    public function __construct(
        private readonly string $transactionId,
        private readonly string $shopperEmailAddress,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
        private readonly string $baseUri = 'https://api.eu.elavonpayments.com',
    ) {
        if (empty($this->transactionId)) {
            throw new InvalidArgumentException('Transaction ID cannot be empty');
        }

        if (empty($this->shopperEmailAddress)) {
            throw new InvalidArgumentException('Shopper email address is required');
        }
    }

    public function build(): RequestInterface
    {
        $requestFactory = $this->requestFactory ?? new Psr17Factory();
        $streamFactory = $this->streamFactory ?? new Psr17Factory();

        $data = ['shopperEmailAddress' => $this->shopperEmailAddress];
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        return $requestFactory
            ->createRequest('POST', $this->baseUri . '/transactions/' . $this->transactionId . '/email-receipt-requests')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withBody($streamFactory->createStream($json));
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    public function getShopperEmailAddress(): string
    {
        return $this->shopperEmailAddress;
    }
}
