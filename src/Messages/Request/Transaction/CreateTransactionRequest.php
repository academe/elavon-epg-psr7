<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Transaction;

use Academe\Elavon\Epg\Psr7\Dtos\Transaction;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;
use Psr\Http\Message\RequestInterface;

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
    use HasPsr17Factories;

    /**
     * @param Transaction|array<string, mixed> $transaction Transaction data
     */
    public function __construct(
        private readonly Transaction|array $transaction,
    ) {
    }

    /**
     * Builds the PSR-7 HTTP request.
     */
    public function build(): RequestInterface
    {
        $body = json_encode($this->getTransaction()->toData(), JSON_THROW_ON_ERROR);

        return $this->getRequestFactory()
            ->createRequest('POST', '/transactions')
            ->withBody($this->getStreamFactory()->createStream($body));
    }

    /**
     * Gets the transaction object, normalizing from array if needed.
     */
    public function getTransaction(): Transaction
    {
        return $this->transaction instanceof Transaction
            ? $this->transaction
            : Transaction::fromData($this->transaction);
    }
}
