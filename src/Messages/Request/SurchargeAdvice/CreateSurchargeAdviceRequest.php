<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\SurchargeAdvice;

use Academe\Elavon\Epg\Psr7\Dtos\SurchargeAdvice;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Create Surcharge Advice Request.
 *
 * Builds a PSR-7 request for creating a surcharge calculation advice (POST /surcharge-advices).
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\CreateSurchargeAdviceRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 * use Academe\Elavon\Epg\Psr7\Dtos\SurchargeAdvice;
 * use Money\Money;
 *
 * // Build the surcharge advice request
 * $surchargeAdvice = new SurchargeAdvice(
 *     total: new Money('100.00', 'USD'),
 *     panToken: 'token123',
 * );
 *
 * $request = (new CreateSurchargeAdviceRequest($surchargeAdvice))->build();
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
class CreateSurchargeAdviceRequest
{
    use HasPsr17Factories;

    /**
     * @param SurchargeAdvice $surchargeAdvice Surcharge advice data     *
     * @throws InvalidArgumentException When surcharge advice data is invalid
     */
    public function __construct(
        public readonly SurchargeAdvice $surchargeAdvice
    ) {
        $this->validate();
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{surchargeAdvice: SurchargeAdvice|array<string, mixed>} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('surchargeAdvice', $data)) {
            throw new InvalidArgumentException("Missing required key 'surchargeAdvice' in data");
        }

        $surchargeAdvice = $data['surchargeAdvice'] instanceof SurchargeAdvice
            ? $data['surchargeAdvice']
            : SurchargeAdvice::fromData($data['surchargeAdvice']);

        return new static($surchargeAdvice);
    }

    /**
     * Validates the surcharge advice request.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        if ($this->surchargeAdvice->total === null) {
            throw new InvalidArgumentException('Total is required to create a surcharge advice');
        }
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Serialize to JSON
        $data = $this->surchargeAdvice->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request
        return $this->getRequestFactory()
            ->createRequest('POST', '/surcharge-advices')
            ->withBody($this->getStreamFactory()->createStream($json));
    }
}
