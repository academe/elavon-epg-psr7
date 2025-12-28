<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Account;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Retrieve Account Request.
 *
 * Builds a PSR-7 request for retrieving a single account (GET /accounts/{id}).
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\Account\RetrieveAccountRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 *
 * // Build the base request
 * $request = (new RetrieveAccountRequest('account123'))->build();
 *
 * // Or build from raw data
 * $request = RetrieveAccountRequest::fromData(['accountId' => 'account123'])->build();
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
 * - Elavon API headers (Accept, Accept-Version)
 * - Environment configuration (sandbox, production, custom base URI)
 * - Authentication headers (Authorization)
 * Use the ElavonApiFactory to add these.
 */
class RetrieveAccountRequest
{
    use HasPsr17Factories;

    /**
     * @param string $accountId Account ID to retrieve
     *
     * @throws InvalidArgumentException When account ID is empty
     */
    public function __construct(
        public readonly string $accountId,
    ) {
        if (empty($this->accountId)) {
            throw new InvalidArgumentException('Account ID cannot be empty');
        }
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{accountId: string} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('accountId', $data)) {
            throw new InvalidArgumentException("Missing required key 'accountId' in data");
        }

        return new static($data['accountId']);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        return $this->getRequestFactory()
            ->createRequest('GET', '/accounts/' . $this->accountId);
    }
}
