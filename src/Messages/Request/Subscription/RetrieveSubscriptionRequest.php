<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Subscription;

use Academe\Elavon\Epg\Psr7\Contracts\RequestMessage;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Retrieve Subscription Request.
 *
 * Builds a PSR-7 request for retrieving a subscription by ID (GET /subscriptions/{id}).
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\Subscription\RetrieveSubscriptionRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 *
 * // Build the base request
 * $request = (new RetrieveSubscriptionRequest('6xxFwvM8BqmM6T6DcF3DyTB3'))->build();
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
class RetrieveSubscriptionRequest implements RequestMessage
{
    use HasPsr17Factories;

    /**
     * @param string $subscriptionId Subscription ID to retrieve     *
     * @throws InvalidArgumentException When subscription ID is empty
     */
    public function __construct(
        public readonly string $subscriptionId
    ) {
        if (empty($this->subscriptionId)) {
            throw new InvalidArgumentException('Subscription ID cannot be empty');
        }
    }

    /**
     * @param array{subscriptionId: string} $data
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('subscriptionId', $data)) {
            throw new InvalidArgumentException("Missing required key 'subscriptionId' in data");
        }

        return new static($data['subscriptionId']);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Build PSR-7 GET request
        return $this->getRequestFactory()
            ->createRequest('GET', '/subscriptions/' . $this->subscriptionId);
    }
}
