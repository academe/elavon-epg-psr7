<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Subscription;

use Academe\Elavon\Epg\Psr7\Dtos\Subscription;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Create Subscription Request.
 *
 * Builds a PSR-7 request for creating a subscription (POST /subscriptions).
 *
 * A subscription associates a shopper with a plan. The subscription will generate
 * recurring payments based on timing and amount details from the plan and using
 * a specific stored card for the payment.
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\Subscription\CreateSubscriptionRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
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
class CreateSubscriptionRequest
{
    use HasPsr17Factories;

    /**
     * @param Subscription $subscription Subscription data     *
     * @throws InvalidArgumentException When subscription data is invalid
     */
    public function __construct(
        public readonly Subscription $subscription
    ) {
        // Validate required fields for creation
        $this->validateSubscriptionRequest($this->subscription);
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{subscription: Subscription|array<string, mixed>} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('subscription', $data)) {
            throw new InvalidArgumentException("Missing required key 'subscription' in data");
        }

        $subscription = $data['subscription'] instanceof Subscription
            ? $data['subscription']
            : Subscription::fromData($data['subscription']);

        return new static($subscription);
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

        // Build PSR-7 POST request
        return $this->getRequestFactory()
            ->createRequest('POST', '/subscriptions')
            ->withBody($this->getStreamFactory()->createStream($json));
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
