<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Subscription;

use Academe\Elavon\Epg\Psr7\Dtos\Subscription;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
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
class UpdateSubscriptionRequest
{
    use HasPsr17Factories;

    private readonly Subscription $subscription;

    /**
     * @param string $subscriptionId Subscription ID to update
     * @param Subscription|array<string, mixed> $subscription Updated subscription data or array     *
     * @throws InvalidArgumentException When subscription ID is empty or subscription data is invalid
     */
    public function __construct(
        private readonly string $subscriptionId,
        Subscription|array $subscription
    ) {
        if (empty($this->subscriptionId)) {
            throw new InvalidArgumentException('Subscription ID cannot be empty');
        }

        // Normalize to Subscription object
        $this->subscription = match (true) {
            $subscription instanceof Subscription => $subscription,
            is_array($subscription) => Subscription::fromData($subscription),
        };
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Use built-in factories if none provided
        $requestFactory = $this->getRequestFactory();
        $streamFactory = $this->getStreamFactory();

        // Serialize subscription to JSON
        $data = $this->subscription->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request (updates use POST, not PUT/PATCH)
        return $requestFactory
            ->createRequest('POST', '/subscriptions/' . $this->subscriptionId)
            ->withBody($streamFactory->createStream($json));
    }

    /**
     * Gets the subscription ID being updated.
     *
     * @return string
     */
    public function getSubscriptionId(): string
    {
        return $this->subscriptionId;
    }

    /**
     * Gets the subscription data being sent.
     *
     * @return Subscription
     */
    public function getSubscription(): Subscription
    {
        return $this->subscription;
    }
}
