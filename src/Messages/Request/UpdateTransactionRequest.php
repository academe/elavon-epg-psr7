<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request;

use Academe\Elavon\Epg\Psr7\Dtos\Transaction;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Update Transaction Request.
 *
 * Builds a PSR-7 request for updating an existing transaction (PATCH /transactions/{id}).
 *
 * Example usage with ElavonApiRequest decorator:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\UpdateTransactionRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiRequest;
 *
 * // Build the base request (partial update - only changed fields)
 * $updates = new Transaction(
 *     description: 'Updated description',
 *     customReference: 'NEW-REF-123'
 * );
 * $request = (new UpdateTransactionRequest('txn123', $updates))->build();
 *
 * // Add Elavon API headers, environment, and authentication
 * $elavonRequest = ElavonApiRequest::create($request)
 *     ->withSandbox()
 *     ->withAuthentication($merchantAlias, $apiKey);
 *
 * // Send the request
 * $response = $httpClient->sendRequest($elavonRequest);
 * ```
 *
 * Note: This class builds the base request but does NOT add:
 * - Elavon API headers (Accept, Content-Type, Accept-Version)
 * - Environment configuration (sandbox, production, custom base URI)
 * - Authentication headers (Authorization)
 * Use the ElavonApiRequest decorator to add these via fluent interface.
 */
class UpdateTransactionRequest
{
    /**
     * @param string $transactionId Transaction ID to update
     * @param Transaction|array<string, mixed> $updates Partial transaction data (only fields to update)
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     * @param StreamFactoryInterface|null $streamFactory PSR-17 stream factory (uses built-in if null)
     * @param string $baseUri Base URI for the API (e.g., "https://api.eu.elavonpayments.com")
     *
     * @throws InvalidArgumentException When transaction ID is empty
     */
    public function __construct(
        private readonly string $transactionId,
        private readonly Transaction|array $updates,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
        private readonly string $baseUri = 'https://api.eu.elavonpayments.com',
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
        $requestFactory = $this->requestFactory ?? new Psr17Factory();
        $streamFactory = $this->streamFactory ?? new Psr17Factory();

        // Normalize to Transaction object
        $updates = $this->updates instanceof Transaction
            ? $this->updates
            : Transaction::fromData($this->updates);

        // Serialize updates to JSON (only non-null fields will be included)
        $body = json_encode($updates->toData(), JSON_THROW_ON_ERROR);

        // Create request stream
        $stream = $streamFactory->createStream($body);

        // Build PSR-7 request
        // Note: PATCH is used for partial updates
        return $requestFactory
            ->createRequest('PATCH', $this->baseUri . '/transactions/' . $this->transactionId)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
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
