<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\HostedCard;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Retrieve Hosted Card Request.
 *
 * Builds a PSR-7 request for retrieving a single hosted card (GET /hosted-cards/{id}).
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\RetrieveHostedCardRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 *
 * // Build the base request
 * $request = (new RetrieveHostedCardRequest('hc123'))->build();
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
class RetrieveHostedCardRequest
{
    use HasPsr17Factories;

    /**
     * @param string $hostedCardId Hosted card ID to retrieve     *
     * @throws InvalidArgumentException When hosted card ID is empty
     */
    public function __construct(
        public readonly string $hostedCardId
    ) {
        if (empty($this->hostedCardId)) {
            throw new InvalidArgumentException('Hosted card ID cannot be empty');
        }
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{hostedCardId: string} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('hostedCardId', $data)) {
            throw new InvalidArgumentException("Missing required key 'hostedCardId' in data");
        }

        return new static($data['hostedCardId']);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Build PSR-7 GET request
        return $this->getRequestFactory()
            ->createRequest('GET', '/hosted-cards/' . $this->hostedCardId);
    }
}
