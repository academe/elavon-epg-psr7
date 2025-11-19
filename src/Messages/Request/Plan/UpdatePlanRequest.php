<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Plan;

use Academe\Elavon\Epg\Psr7\Dtos\Plan;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Update Plan Request.
 *
 * Builds a PSR-7 request for updating an existing plan (POST /plans/{id}).
 *
 * This operation overwrites an existing plan resource.
 *
 * Example usage with ElavonApiRequest decorator:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\Plan\UpdatePlanRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiRequest;
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
class UpdatePlanRequest
{
    private readonly Plan $plan;

    /**
     * @param string $planId Plan ID to update
     * @param Plan|array<string, mixed> $plan Updated plan data or array
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     * @param StreamFactoryInterface|null $streamFactory PSR-17 stream factory (uses built-in if null)
     * @param string $baseUri Base URI for the API (e.g., "https://api.eu.elavonpayments.com")
     *
     * @throws InvalidArgumentException When plan ID is empty or plan data is invalid
     */
    public function __construct(
        private readonly string $planId,
        Plan|array $plan,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
        private readonly string $baseUri = 'https://api.eu.elavonpayments.com',
    ) {
        if (empty($this->planId)) {
            throw new InvalidArgumentException('Plan ID cannot be empty');
        }

        // Normalize to Plan object
        $this->plan = match (true) {
            $plan instanceof Plan => $plan,
            is_array($plan) => Plan::fromData($plan),
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
        $requestFactory = $this->requestFactory ?? new Psr17Factory();
        $streamFactory = $this->streamFactory ?? new Psr17Factory();

        // Serialize plan to JSON
        $data = $this->plan->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request (updates use POST, not PUT/PATCH)
        return $requestFactory
            ->createRequest('POST', $this->baseUri . '/plans/' . $this->planId)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withBody($streamFactory->createStream($json));
    }

    /**
     * Gets the plan ID being updated.
     *
     * @return string
     */
    public function getPlanId(): string
    {
        return $this->planId;
    }

    /**
     * Gets the plan data being sent.
     *
     * @return Plan
     */
    public function getPlan(): Plan
    {
        return $this->plan;
    }
}
