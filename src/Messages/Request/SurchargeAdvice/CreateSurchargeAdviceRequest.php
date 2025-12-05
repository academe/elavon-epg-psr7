<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\SurchargeAdvice;

use Academe\Elavon\Epg\Psr7\Dtos\SurchargeAdvice;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

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
    private readonly SurchargeAdvice $surchargeAdvice;

    /**
     * @param SurchargeAdvice|array<string, mixed> $surchargeAdvice Surcharge advice data
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     * @param StreamFactoryInterface|null $streamFactory PSR-17 stream factory (uses built-in if null)
     *
     * @throws InvalidArgumentException When surcharge advice data is invalid
     */
    public function __construct(
        SurchargeAdvice|array $surchargeAdvice,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
    ) {
        // Normalize to SurchargeAdvice object
        $this->surchargeAdvice = match (true) {
            $surchargeAdvice instanceof SurchargeAdvice => $surchargeAdvice,
            is_array($surchargeAdvice) => SurchargeAdvice::fromData($surchargeAdvice),
        };

        $this->validate();
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
        // Use built-in factories if none provided
        $requestFactory = $this->requestFactory ?? new Psr17Factory();
        $streamFactory = $this->streamFactory ?? new Psr17Factory();

        // Serialize to JSON
        $data = $this->surchargeAdvice->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request
        return $requestFactory
            ->createRequest('POST', '/surcharge-advices')
            ->withBody($streamFactory->createStream($json));
    }

    /**
     * Gets the surcharge advice data.
     *
     * @return SurchargeAdvice
     */
    public function getSurchargeAdvice(): SurchargeAdvice
    {
        return $this->surchargeAdvice;
    }
}
