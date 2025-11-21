<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Subscription;

use Academe\Elavon\Epg\Psr7\Dtos\Subscription;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Create Subscription Request.
 *
 * Builds a PSR-7 request for creating a subscription (POST /subscriptions).
 *
 * A subscription associates a shopper with a plan. The subscription will generate
 * recurring payments based on timing and amount details from the plan and using
 * a specific stored card for the payment.
 *
 * Example usage with ElavonApiRequest decorator:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\Subscription\CreateSubscriptionRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiRequest;
 * use Academe\Elavon\Epg\Psr7\Dtos\Subscription;
 *
 * // Build the subscription
 * $subscription = new Subscription(
 *     plan: 'https://api.eu.elavonpayments.com/plans/6xxFwvM8BqmM6T6DcF3DyTB3',
 *     storedCard: 'https://api.eu.elavonpayments.com/stored-cards/abc123',
 *     firstBillAt: '2025-01-01',
 *     timeZoneId: 'Europe/London',
 * );
 *
 * // Build the request
 * $request = (new CreateSubscriptionRequest($subscription))->build();
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
 * - Elavon API headers (Accept, Accept-Version)
 * - Environment configuration (sandbox, production, custom base URI)
 * - Authentication headers (Authorization)
 * Use the ElavonApiRequest decorator to add these via fluent interface.
 */
class CreateSubscriptionRequest
{
    private readonly Subscription $subscription;

    /**
     * @param Subscription|array<string, mixed> $subscription Subscription data or array
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     * @param StreamFactoryInterface|null $streamFactory PSR-17 stream factory (uses built-in if null)
     * @param string $baseUri Base URI for the API (e.g., "https://api.eu.elavonpayments.com")
     *
     * @throws InvalidArgumentException When subscription data is invalid
     */
    public function __construct(
        Subscription|array $subscription,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
        private readonly string $baseUri = 'https://api.eu.elavonpayments.com',
    ) {
        // Normalize to Subscription object
        $this->subscription = match (true) {
            $subscription instanceof Subscription => $subscription,
            is_array($subscription) => Subscription::fromData($subscription),
        };

        // Validate required fields for creation
        $this->validateSubscriptionRequest($this->subscription);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Use built-in factories if none provided
        $requestFactory = $this->requestFactory ?? new Psr17Factory();
        $streamFactory = $this->streamFactory ?? new Psr17Factory();

        // Serialize subscription to JSON
        $data = $this->subscription->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request
        return $requestFactory
            ->createRequest('POST', $this->baseUri . '/subscriptions')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withBody($streamFactory->createStream($json));
    }

    /**
     * Gets the subscription being created.
     *
     * @return Subscription
     */
    public function getSubscription(): Subscription
    {
        return $this->subscription;
    }

    /**
     * Validates that required fields are present for a subscription creation request.
     *
     * @param Subscription $subscription
     * @throws InvalidArgumentException When required fields are missing
     */
    private function validateSubscriptionRequest(Subscription $subscription): void
    {
        // According to OpenAPI spec, 'plan', 'storedCard', 'firstBillAt', and 'timeZoneId' are required for SubscriptionInput
        if ($subscription->plan === null) {
            throw new InvalidArgumentException('Subscription plan is required for creating a subscription');
        }

        if ($subscription->storedCard === null && $subscription->storedAchPayment === null) {
            throw new InvalidArgumentException('Subscription storedCard or storedAchPayment is required for creating a subscription');
        }

        if ($subscription->firstBillAt === null) {
            throw new InvalidArgumentException('Subscription firstBillAt is required for creating a subscription');
        }

        if ($subscription->timeZoneId === null) {
            throw new InvalidArgumentException('Subscription timeZoneId is required for creating a subscription');
        }
    }
}
