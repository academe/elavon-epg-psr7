<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Transaction;

use Academe\Elavon\Epg\Psr7\Dtos\Transaction;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
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
 * // Build the base request (partial update - only changed fields)
 * $updates = new Transaction(
 *     description: 'Updated description',
 *     customReference: 'NEW-REF-123'
 * );
 * $request = (new UpdateTransactionRequest('txn123', $updates))->build();
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
class UpdateTransactionRequest
{
    use HasPsr17Factories;

    /**
     * @param string $transactionId Transaction ID to update
     * @param Transaction|array<string, mixed> $updates Partial transaction data (only fields to update)     *
     * @throws InvalidArgumentException When transaction ID is empty
     */
    public function __construct(
        private readonly string $transactionId,
        private readonly Transaction|array $updates
    ) {
        if (empty($this->transactionId)) {
            throw new InvalidArgumentException('Transaction ID cannot be empty');
        }
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     *
     * @throws InvalidArgumentException When request cannot be built
     */
    public function build(): RequestInterface
    {
        // Use built-in factory if none provided

        // Normalize to Transaction object
        $updates = $this->updates instanceof Transaction
            ? $this->updates
            : Transaction::fromData($this->updates);

        // Serialize updates to JSON (only non-null fields will be included)
        $body = json_encode($updates->toData(), JSON_THROW_ON_ERROR);

        // Create request stream
        $stream = $this->getStreamFactory()->createStream($body);

        // Build PSR-7 request
        // Note: PATCH is used for partial updates
        return $this->getRequestFactory()
            ->createRequest('PATCH', '/transactions/' . $this->transactionId)
            ->withBody($stream);
    }

    /**
     * Gets the transaction ID being updated.
     *
     * @return string
     */
    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    /**
     * Gets the update data.
     *
     * @return Transaction
     */
    public function getUpdates(): Transaction
    {
        return $this->updates instanceof Transaction
            ? $this->updates
            : Transaction::fromData($this->updates);
    }
}
