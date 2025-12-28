<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\ForexAdvice;

use Academe\Elavon\Epg\Psr7\Dtos\ForexAdvice;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Create Forex Advice Request.
 *
 * Builds a PSR-7 request for creating a foreign exchange conversion advice (POST /forex-advices).
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\CreateForexAdviceRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 * use Academe\Elavon\Epg\Psr7\Dtos\ForexAdvice;
 * use Money\Money;
 *
 * // Build the forex advice request
 * $forexAdvice = new ForexAdvice(
 *     total: new Money('100.00', 'USD'),
 *     cardNumber: '4111111111111111',
 *     shopperInteraction: 'ecommerce',
 * );
 *
 * $request = (new CreateForexAdviceRequest($forexAdvice))->build();
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
class CreateForexAdviceRequest
{
    use HasPsr17Factories;

    /**
     * @param ForexAdvice $forexAdvice Forex advice data     *
     * @throws InvalidArgumentException When forex advice data is invalid
     */
    public function __construct(
        public readonly ForexAdvice $forexAdvice
    ) {
        $this->validate();
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{forexAdvice: ForexAdvice|array<string, mixed>} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('forexAdvice', $data)) {
            throw new InvalidArgumentException("Missing required key 'forexAdvice' in data");
        }

        $forexAdvice = $data['forexAdvice'] instanceof ForexAdvice
            ? $data['forexAdvice']
            : ForexAdvice::fromData($data['forexAdvice']);

        return new static($forexAdvice);
    }

    /**
     * Validates the forex advice request.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        if ($this->forexAdvice->total === null) {
            throw new InvalidArgumentException('Total is required to create a forex advice');
        }

        if ($this->forexAdvice->shopperInteraction === null) {
            throw new InvalidArgumentException('Shopper interaction is required to create a forex advice');
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
        $data = $this->forexAdvice->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request
        return $this->getRequestFactory()
            ->createRequest('POST', '/forex-advices')
            ->withBody($this->getStreamFactory()->createStream($json));
    }
}
