<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Shopper;

use Academe\Elavon\Epg\Psr7\Contracts\RequestMessage;
use Academe\Elavon\Epg\Psr7\Dtos\Shopper;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
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
class UpdateShopperRequest implements RequestMessage
{
    use HasPsr17Factories;

    /**
     * @param string $shopperId shopper ID to update
     * @param Shopper $shopper Update data (partial stored card)     *
     * @throws InvalidArgumentException When shopper ID is empty or updates are invalid
     */
    public function __construct(
        public readonly string $shopperId,
        public readonly Shopper $shopper
    ) {
        if (empty($this->shopperId)) {
            throw new InvalidArgumentException('Shopper ID cannot be empty');
        }
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{shopperId: string, shopper: Shopper|array<string, mixed>} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('shopperId', $data)) {
            throw new InvalidArgumentException("Missing required key 'shopperId' in data");
        }

        if (! array_key_exists('shopper', $data)) {
            throw new InvalidArgumentException("Missing required key 'shopper' in data");
        }

        $shopper = $data['shopper'] instanceof Shopper
            ? $data['shopper']
            : Shopper::fromData($data['shopper']);

        return new static($data['shopperId'], $shopper);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Serialize updates to JSON
        $data = $this->shopper->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 PATCH request
        return $this->getRequestFactory()
            ->createRequest('PATCH', '/shoppers/' . $this->shopperId)
            ->withBody($this->getStreamFactory()->createStream($json));
    }
}
