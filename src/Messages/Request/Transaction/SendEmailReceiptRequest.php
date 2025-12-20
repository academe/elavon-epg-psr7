<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Transaction;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

class SendEmailReceiptRequest
{
    use HasPsr17Factories;

    public function __construct(
        private readonly string $transactionId,
        private readonly string $shopperEmailAddress
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
        $data = ['shopperEmailAddress' => $this->shopperEmailAddress];
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        return $this->getRequestFactory()
            ->createRequest('POST', '/transactions/' . $this->transactionId . '/email-receipt-requests')
            ->withBody($this->getStreamFactory()->createStream($json));
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
