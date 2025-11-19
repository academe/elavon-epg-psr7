<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request;

use Academe\Elavon\Epg\Psr7\Dtos\Shopper;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Update Shopper Request.
 *
 * Builds a PSR-7 request for updating a stored card (PATCH /shoppers/{id}).
 *
 * This supports partial updates - only the fields provided will be updated.
 *
 * Example usage with ElavonApiRequest decorator:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\UpdateShopperRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiRequest;
 * use Academe\Elavon\Epg\Psr7\Dtos\Shopper;
 *
 * // Build the updates
 * $updates = new Shopper(
 *     customReference: 'new-reference-123',
 *     customFields: ['status' => 'active'],
 * );
 *
 * // Build the request
 * $request = (new UpdateShopperRequest('sc123', $updates))->build();
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
class UpdateShopperRequest
{
    private readonly Shopper $updates;

    /**
     * @param string $storedCardId shopper ID to update
     * @param Shopper|array<string, mixed> $updates Update data (partial stored card)
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     * @param StreamFactoryInterface|null $streamFactory PSR-17 stream factory (uses built-in if null)
     * @param string $baseUri Base URI for the API (e.g., "https://api.eu.elavonpayments.com")
     *
     * @throws InvalidArgumentException When stored card ID is empty or updates are invalid
     */
    public function __construct(
        private readonly string $storedCardId,
        Shopper|array $updates,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
        private readonly string $baseUri = 'https://api.eu.elavonpayments.com',
    ) {
        if (empty($this->storedCardId)) {
            throw new InvalidArgumentException('shopper ID cannot be empty');
        }

        // Normalize to Shopper object
        $this->updates = match (true) {
            $updates instanceof Shopper => $updates,
            is_array($updates) => Shopper::fromData($updates),
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

        // Serialize updates to JSON
        $data = $this->updates->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 PATCH request
        return $requestFactory
            ->createRequest('PATCH', $this->baseUri . '/shoppers/' . $this->storedCardId)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withBody($streamFactory->createStream($json));
    }

    /**
     * Gets the stored card ID being updated.
     *
     * @return string
     */
    public function getShopperId(): string
    {
        return $this->storedCardId;
    }

    /**
     * Gets the update data.
     *
     * @return Shopper
     */
    public function getUpdates(): Shopper
    {
        return $this->updates;
    }
}
