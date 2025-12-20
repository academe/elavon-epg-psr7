<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Plan;

use Academe\Elavon\Epg\Psr7\Dtos\Plan;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Create Plan Request.
 *
 * Builds a PSR-7 request for creating a plan (POST /plans).
 *
 * Plans provide templates for recurring billing, defining the amount, frequency,
 * and other billing details. See also subscriptions, which associate shoppers with plans.
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\Plan\CreatePlanRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 * use Academe\Elavon\Epg\Psr7\Dtos\Plan;
 *
 * // Build the plan
 * $plan = new Plan(
 *     name: 'Monthly Software License',
 *     description: 'Single user license billed monthly',
 *     billingInterval: ['timeUnit' => 'month', 'count' => 1],
 *     total: ['amount' => '29.99', 'currencyCode' => 'USD'],
 * );
 *
 * // Build the request
 * $request = (new CreatePlanRequest($plan))->build();
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
class CreatePlanRequest
{
    use HasPsr17Factories;

    private readonly Plan $plan;

    /**
     * @param Plan|array<string, mixed> $plan Plan data or array     *
     * @throws InvalidArgumentException When plan data is invalid
     */
    public function __construct(
        Plan|array $plan
    ) {
        // Normalize to Plan object
        $this->plan = match (true) {
            $plan instanceof Plan => $plan,
            is_array($plan) => Plan::fromData($plan),
        };

        // Validate required fields for creation
        $this->validatePlanRequest($this->plan);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Use built-in factories if none provided

        // Serialize plan to JSON
        $data = $this->plan->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request
        return $this->getRequestFactory()
            ->createRequest('POST', '/plans')
            ->withBody($this->getStreamFactory()->createStream($json));
    }

    /**
     * Gets the plan being created.
     *
     * @return Plan
     */
    public function getPlan(): Plan
    {
        return $this->plan;
    }

    /**
     * Validates that required fields are present for a plan creation request.
     *
     * @param Plan $plan
     * @throws InvalidArgumentException When required fields are missing
     */
    private function validatePlanRequest(Plan $plan): void
    {
        // According to OpenAPI spec, 'name', 'billingInterval', and 'total' are required for PlanInput
        if ($plan->name === null) {
            throw new InvalidArgumentException('Plan name is required for creating a plan');
        }

        if ($plan->billingInterval === null) {
            throw new InvalidArgumentException('Plan billingInterval is required for creating a plan');
        }

        if ($plan->total === null) {
            throw new InvalidArgumentException('Plan total is required for creating a plan');
        }
    }
}
