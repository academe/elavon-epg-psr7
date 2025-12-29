<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Transaction;

use Academe\Elavon\Epg\Psr7\Contracts\RequestMessage;
use Academe\Elavon\Epg\Psr7\Dtos\Transaction;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Update Transaction Request.
 *
 * Builds a PSR-7 request for updating an existing transaction (PATCH /transactions/{id}).
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\UpdateTransactionRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 *
 * // Build the base request using hydrated objects (partial update - only changed fields)
 * $updates = new Transaction(
 *     description: 'Updated description',
 *     customReference: 'NEW-REF-123'
 * );
 * $request = (new UpdateTransactionRequest('txn123', $updates))->build();
 *
 * // Or build from raw data
 * $request = UpdateTransactionRequest::fromData([
 *     'transactionId' => 'txn123',
 *     'transaction' => ['description' => 'Updated description'],
 * ])->build();
 *
 * // Add Elavon API headers, environment, and authentication
 * $factory = ElavonApiFactory::configure()
 *     ->withRegion('eu')
 *     ->withEnvironment('sandbox')
 *     ->withAuthentication($merchantAlias, $apiKey);
 *
 * // Send the request
 * $apiRequest = $factory->apply($request);
 * $response = $httpClient->sendRequest($apiRequest);
 * ```
 *
 * Note: This class builds the base request but does NOT add:
 * - Elavon API headers (Accept, Content-Type, Accept-Version)
 * - Environment configuration (sandbox, production, custom base URI)
 * - Authentication headers (Authorization)
 * Use the ElavonApiFactory to add these.
 */
class UpdateTransactionRequest implements RequestMessage
{
    use HasPsr17Factories;

    /**
     * @param string $transactionId Transaction ID to update
     * @param Transaction $transaction Partial transaction data (only fields to update)
     *
     * @throws InvalidArgumentException When transaction ID is empty
     */
    public function __construct(
        public readonly string $transactionId,
        public readonly Transaction $transaction,
    ) {
        if (empty($this->transactionId)) {
            throw new InvalidArgumentException('Transaction ID cannot be empty');
        }
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{transactionId: string, transaction: Transaction|array<string, mixed>} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('transactionId', $data)) {
            throw new InvalidArgumentException("Missing required key 'transactionId' in data");
        }

        if (! array_key_exists('transaction', $data)) {
            throw new InvalidArgumentException("Missing required key 'transaction' in data");
        }

        $transaction = $data['transaction'] instanceof Transaction
            ? $data['transaction']
            : Transaction::fromData($data['transaction']);

        return new static($data['transactionId'], $transaction);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        $body = json_encode($this->transaction->toData(), JSON_THROW_ON_ERROR);

        return $this->getRequestFactory()
            ->createRequest('PATCH', '/transactions/' . $this->transactionId)
            ->withBody($this->getStreamFactory()->createStream($body));
    }
}
