<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Plan;

use Academe\Elavon\Epg\Psr7\Contracts\RequestMessage;
use Academe\Elavon\Epg\Psr7\Dtos\Plan;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Update Plan Request.
 *
 * Builds a PSR-7 request for updating an existing plan (POST /plans/{id}).
 *
 * This operation overwrites an existing plan resource.
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\Plan\UpdatePlanRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 * use Academe\Elavon\Epg\Psr7\Dtos\Plan;
 *
 * // Build the updated plan
 * $plan = new Plan(
 *     name: 'Updated Monthly License',
 *     description: 'Updated description',
 *     billingInterval: ['timeUnit' => 'month', 'count' => 1],
 *     total: ['amount' => '39.99', 'currencyCode' => 'USD'],
 * );
 *
 * // Build the request
 * $request = (new UpdatePlanRequest('6xxFwvM8BqmM6T6DcF3DyTB3', $plan))->build();
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
class UpdatePlanRequest implements RequestMessage
{
    use HasPsr17Factories;

    /**
     * @param string $planId Plan ID to update
     * @param Plan $plan Updated plan data     *
     * @throws InvalidArgumentException When plan ID is empty
     */
    public function __construct(
        public readonly string $planId,
        public readonly Plan $plan
    ) {
        if (empty($this->planId)) {
            throw new InvalidArgumentException('Plan ID cannot be empty');
        }
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{planId: string, plan: Plan|array<string, mixed>} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('planId', $data)) {
            throw new InvalidArgumentException("Missing required key 'planId' in data");
        }

        if (! array_key_exists('plan', $data)) {
            throw new InvalidArgumentException("Missing required key 'plan' in data");
        }

        $plan = $data['plan'] instanceof Plan
            ? $data['plan']
            : Plan::fromData($data['plan']);

        return new static($data['planId'], $plan);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Serialize plan to JSON
        $data = $this->plan->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request (updates use POST, not PUT/PATCH)
        return $this->getRequestFactory()
            ->createRequest('POST', '/plans/' . $this->planId)
            ->withBody($this->getStreamFactory()->createStream($json));
    }
}
