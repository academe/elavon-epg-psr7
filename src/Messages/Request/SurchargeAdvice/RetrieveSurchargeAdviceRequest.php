<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\SurchargeAdvice;

use Academe\Elavon\Epg\Psr7\Contracts\RequestMessage;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Retrieve Surcharge Advice Request.
 *
 * Builds a PSR-7 request for retrieving a single surcharge advice (GET /surcharge-advices/{id}).
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\RetrieveSurchargeAdviceRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 *
 * // Build the base request
 * $request = (new RetrieveSurchargeAdviceRequest('sca123'))->build();
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
class RetrieveSurchargeAdviceRequest implements RequestMessage
{
    use HasPsr17Factories;

    /**
     * @param string $surchargeAdviceId Surcharge advice ID to retrieve     *
     * @throws InvalidArgumentException When surcharge advice ID is empty
     */
    public function __construct(
        public readonly string $surchargeAdviceId
    ) {
        if (empty($this->surchargeAdviceId)) {
            throw new InvalidArgumentException('Surcharge advice ID cannot be empty');
        }
    }

    /**
     * @param array{surchargeAdviceId: string} $data
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('surchargeAdviceId', $data)) {
            throw new InvalidArgumentException("Missing required key 'surchargeAdviceId' in data");
        }

        return new static($data['surchargeAdviceId']);
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
            ->createRequest('GET', '/surcharge-advices/' . $this->surchargeAdviceId);
    }
}
