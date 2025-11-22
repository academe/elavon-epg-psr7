<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Shopper;

use Academe\Elavon\Epg\Psr7\Dtos\Shopper;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Create Shopper Request.
 *
 * Builds a PSR-7 request for creating a stored card (POST /shoppers).
 *
 * shoppers allow merchants to charge customers for recurring payments without requiring
 * them to re-enter card details. The card can be initialized from a hosted card or provided directly.
 *
 * Example usage with ElavonApiRequest decorator:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\CreateShopperRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiRequest;
 * use Academe\Elavon\Epg\Psr7\Dtos\Shopper;
 *
 * // Build the stored card
 * $storedCard = new Shopper(
 *     shopper: 'https://api.example.com/shoppers/s123',
 *     hostedCard: 'https://api.example.com/hosted-cards/hc456',
 *     customReference: 'customer-card-789',
 * );
 *
 * // Build the request
 * $request = (new CreateShopperRequest($storedCard))->build();
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
class CreateShopperRequest
{
    private readonly Shopper $shopper;

    /**
     * @param Shopper|array<string, mixed> $shopper shopper data or array
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     * @param StreamFactoryInterface|null $streamFactory PSR-17 stream factory (uses built-in if null)
     *
     * @throws InvalidArgumentException When stored card data is invalid
     */
    public function __construct(
        Shopper|array $shopper,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
    ) {
        // Normalize to Shopper object
        $this->shopper = match (true) {
            $shopper instanceof Shopper => $shopper,
            is_array($shopper) => Shopper::fromData($shopper),
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

        // Serialize stored card to JSON
        $data = $this->shopper->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request
        return $requestFactory
            ->createRequest('POST', '/shoppers')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withBody($streamFactory->createStream($json));
    }

    /**
     * Gets the shopper being created.
     *
     * @return Shopper
     */
    public function getShopper(): Shopper
    {
        return $this->shopper;
    }
}
