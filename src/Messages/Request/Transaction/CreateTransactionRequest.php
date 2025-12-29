<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Transaction;

use Academe\Elavon\Epg\Psr7\Contracts\RequestMessage;
use Academe\Elavon\Epg\Psr7\Dtos\Transaction;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
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
 * // Build the base request using a hydrated Transaction object
 * $request = (new CreateTransactionRequest($transaction))->build();
 *
 * // Or build from raw data
 * $request = CreateTransactionRequest::fromData([
 *     'transaction' => ['total' => ['amount' => '100.00', 'currencyCode' => 'USD']],
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
class CreateTransactionRequest implements RequestMessage
{
    use HasPsr17Factories;

    /**
     * @param Transaction $transaction Transaction data
     */
    public function __construct(
        public readonly Transaction $transaction,
    ) {
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{transaction: Transaction|array<string, mixed>} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('transaction', $data)) {
            throw new InvalidArgumentException("Missing required key 'transaction' in data");
        }

        $transaction = $data['transaction'] instanceof Transaction
            ? $data['transaction']
            : Transaction::fromData($data['transaction']);

        return new static($transaction);
    }

    /**
     * Builds the PSR-7 HTTP request.
     */
    public function build(): RequestInterface
    {
        $body = json_encode($this->transaction->toData(), JSON_THROW_ON_ERROR);

        return $this->getRequestFactory()
            ->createRequest('POST', '/transactions')
            ->withBody($this->getStreamFactory()->createStream($body));
    }
}
