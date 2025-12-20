<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Shopper;

use Academe\Elavon\Epg\Psr7\Dtos\Shopper;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Update Shopper Request.
 *
 * Builds a PSR-7 request for updating a stored card (PATCH /shoppers/{id}).
 *
 * This supports partial updates - only the fields provided will be updated.
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\UpdateShopperRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
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
class UpdateShopperRequest
{
    use HasPsr17Factories;

    private readonly Shopper $updates;

    /**
     * @param string $shopperId shopper ID to update
     * @param Shopper|array<string, mixed> $updates Update data (partial stored card)     *
     * @throws InvalidArgumentException When stored card ID is empty or updates are invalid
     */
    public function __construct(
        private readonly string $shopperId,
        Shopper|array $updates
    ) {
        if (empty($this->shopperId)) {
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

        // Serialize updates to JSON
        $data = $this->updates->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 PATCH request
        return $this->getRequestFactory()
            ->createRequest('PATCH', '/shoppers/' . $this->shopperId)
            ->withBody($this->getStreamFactory()->createStream($json));
    }

    /**
     * Gets the shopper ID being updated.
     *
     * @return string
     */
    public function getShopperId(): string
    {
        return $this->shopperId;
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
