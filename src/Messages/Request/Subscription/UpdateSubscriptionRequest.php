<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Subscription;

use Academe\Elavon\Epg\Psr7\Contracts\RequestMessage;
use Academe\Elavon\Epg\Psr7\Dtos\Subscription;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Update Subscription Request.
 *
 * Builds a PSR-7 request for updating an existing subscription (POST /subscriptions/{id}).
 *
 * This operation overwrites an existing subscription resource.
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\Subscription\UpdateSubscriptionRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 * use Academe\Elavon\Epg\Psr7\Dtos\Subscription;
 *
 * // Build the updated subscription
 * $subscription = new Subscription(
 *     plan: 'https://api.eu.elavonpayments.com/plans/6xxFwvM8BqmM6T6DcF3DyTB3',
 *     storedCard: 'https://api.eu.elavonpayments.com/stored-cards/newcard456',
 *     firstBillAt: '2025-02-01',
 *     timeZoneId: 'Europe/London',
 *     cancelAfterBillNumber: 12,
 * );
 *
 * // Build the request
 * $request = (new UpdateSubscriptionRequest('sub123', $subscription))->build();
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
class UpdateSubscriptionRequest implements RequestMessage
{
    use HasPsr17Factories;

    /**
     * @param string $subscriptionId Subscription ID to update
     * @param Subscription $subscription Updated subscription data     *
     * @throws InvalidArgumentException When subscription ID is empty or subscription data is invalid
     */
    public function __construct(
        public readonly string $subscriptionId,
        public readonly Subscription $subscription
    ) {
        if (empty($this->subscriptionId)) {
            throw new InvalidArgumentException('Subscription ID cannot be empty');
        }
    }

    /**
     * @param array{subscriptionId: string, subscription: Subscription|array<string, mixed>} $data
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('subscriptionId', $data)) {
            throw new InvalidArgumentException("Missing required key 'subscriptionId' in data");
        }

        if (! array_key_exists('subscription', $data)) {
            throw new InvalidArgumentException("Missing required key 'subscription' in data");
        }

        $subscription = $data['subscription'] instanceof Subscription
            ? $data['subscription']
            : Subscription::fromData($data['subscription']);

        return new static($data['subscriptionId'], $subscription);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Serialize subscription to JSON
        $data = $this->subscription->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request (updates use POST, not PUT/PATCH)
        return $this->getRequestFactory()
            ->createRequest('POST', '/subscriptions/' . $this->subscriptionId)
            ->withBody($this->getStreamFactory()->createStream($json));
    }
}
