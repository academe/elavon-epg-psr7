<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Transaction;

use Academe\Elavon\Epg\Psr7\Dtos\Transaction;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Create Transaction Request.
 *
 * Builds a PSR-7 request for creating a new transaction (POST /transactions).
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\CreateTransactionRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 *
 * // Build the base request
 * $request = (new CreateTransactionRequest($transaction))->build();
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
class CreateTransactionRequest
{
    /**
     * Default required fields for creating a transaction.
     * Override via constructor if different fields are required for your use case.
     *
     * @var array<string>
     */
    private const DEFAULT_REQUIRED_FIELDS = [
        'total',
        'card',  // Required for card transactions
    ];

    /**
     * @param Transaction|array<string, mixed> $transaction Transaction data
     * @param array<string>|null $requiredFields List of required field names (uses DEFAULT_REQUIRED_FIELDS if null)
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     * @param StreamFactoryInterface|null $streamFactory PSR-17 stream factory (uses built-in if null)
     *
     * @throws InvalidArgumentException When transaction data is invalid
     */
    public function __construct(
        private readonly Transaction|array $transaction,
        private readonly ?array $requiredFields = null,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
    ) {
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
        $transaction = $this->transaction instanceof Transaction
            ? $this->transaction
            : Transaction::fromData($this->transaction);

        // Validate required fields for request
        $this->validateTransactionRequest($transaction);

        // Serialize transaction to JSON
        $body = json_encode($transaction->toData(), JSON_THROW_ON_ERROR);

        // Create request stream
        $stream = $streamFactory->createStream($body);

        // Build PSR-7 request
        return $requestFactory
            ->createRequest('POST', '/transactions')
            ->withBody($stream);
    }

    /**
     * Validates that required fields are present for a transaction request.
     *
     * @param Transaction $transaction
     * @throws InvalidArgumentException When required fields are missing
     */
    private function validateTransactionRequest(Transaction $transaction): void
    {
        $fieldsToCheck = $this->requiredFields ?? self::DEFAULT_REQUIRED_FIELDS;

        foreach ($fieldsToCheck as $field) {
            if (!property_exists($transaction, $field)) {
                throw new InvalidArgumentException("Unknown required field: {$field}");
            }

            if ($transaction->$field === null) {
                throw new InvalidArgumentException("Transaction {$field} is required for creating a transaction");
            }
        }
    }

    /**
     * Gets the transaction data.
     *
     * @return Transaction
     */
    public function getTransaction(): Transaction
    {
        return $this->transaction instanceof Transaction
            ? $this->transaction
            : Transaction::fromData($this->transaction);
    }
}