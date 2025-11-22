<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\ForexAdvice;

use Academe\Elavon\Epg\Psr7\Dtos\ForexAdvice;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Create Forex Advice Request.
 *
 * Builds a PSR-7 request for creating a foreign exchange conversion advice (POST /forex-advices).
 *
 * Example usage with ElavonApiRequest decorator:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\CreateForexAdviceRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiRequest;
 * use Academe\Elavon\Epg\Psr7\Dtos\ForexAdvice;
 * use Academe\Elavon\Epg\Psr7\ValueObjects\Money;
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
class CreateForexAdviceRequest
{
    private readonly ForexAdvice $forexAdvice;

    /**
     * @param ForexAdvice|array<string, mixed> $forexAdvice Forex advice data
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     * @param StreamFactoryInterface|null $streamFactory PSR-17 stream factory (uses built-in if null)
     *
     * @throws InvalidArgumentException When forex advice data is invalid
     */
    public function __construct(
        ForexAdvice|array $forexAdvice,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
    ) {
        // Normalize to ForexAdvice object
        $this->forexAdvice = match (true) {
            $forexAdvice instanceof ForexAdvice => $forexAdvice,
            is_array($forexAdvice) => ForexAdvice::fromData($forexAdvice),
        };

        $this->validate();
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
        // Use built-in factories if none provided
        $requestFactory = $this->requestFactory ?? new Psr17Factory();
        $streamFactory = $this->streamFactory ?? new Psr17Factory();

        // Serialize to JSON
        $data = $this->forexAdvice->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request
        return $requestFactory
            ->createRequest('POST', '/forex-advices')
            ->withBody($streamFactory->createStream($json));
    }

    /**
     * Gets the forex advice data.
     *
     * @return ForexAdvice
     */
    public function getForexAdvice(): ForexAdvice
    {
        return $this->forexAdvice;
    }
}
