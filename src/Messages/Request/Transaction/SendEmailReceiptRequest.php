<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Transaction;

use Academe\Elavon\Epg\Psr7\Contracts\RequestMessage;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

class SendEmailReceiptRequest implements RequestMessage
{
    use HasPsr17Factories;

    public function __construct(
        public readonly string $transactionId,
        public readonly string $shopperEmailAddress
    ) {
        if (empty($this->transactionId)) {
            throw new InvalidArgumentException('Transaction ID cannot be empty');
        }

        if (empty($this->shopperEmailAddress)) {
            throw new InvalidArgumentException('Shopper email address is required');
        }
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{transactionId: string, shopperEmailAddress: string} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('transactionId', $data)) {
            throw new InvalidArgumentException("Missing required key 'transactionId' in data");
        }

        if (! array_key_exists('shopperEmailAddress', $data)) {
            throw new InvalidArgumentException("Missing required key 'shopperEmailAddress' in data");
        }

        return new static($data['transactionId'], $data['shopperEmailAddress']);
    }

    public function build(): RequestInterface
    {
        $data = ['shopperEmailAddress' => $this->shopperEmailAddress];
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        return $this->getRequestFactory()
            ->createRequest('POST', '/transactions/' . $this->transactionId . '/email-receipt-requests')
            ->withBody($this->getStreamFactory()->createStream($json));
    }
}
