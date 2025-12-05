<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\HostedCard;

use Academe\Elavon\Epg\Psr7\Dtos\HostedCard;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Create Hosted Card Request.
 *
 * Builds a PSR-7 request for creating a hosted card (POST /hosted-cards).
 *
 * Hosted cards allow secure card data collection without the merchant handling sensitive data.
 * The card data is collected and stored temporarily for single-use in a transaction.
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\CreateHostedCardRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 * use Academe\Elavon\Epg\Psr7\Dtos\HostedCard;
 * use Academe\Elavon\Epg\Psr7\Dtos\Card;
 *
 * // Build the hosted card
 * $card = new Card(
 *     number: '4111111111111111',
 *     securityCode: '123',
 *     expirationMonth: 12,
 *     expirationYear: 2025,
 * );
 * $hostedCard = new HostedCard(card: $card);
 *
 * // Build the request
 * $request = (new CreateHostedCardRequest($hostedCard))->build();
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
class CreateHostedCardRequest
{
    private readonly HostedCard $hostedCard;

    /**
     * @param HostedCard|array<string, mixed> $hostedCard Hosted card data or array
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     * @param StreamFactoryInterface|null $streamFactory PSR-17 stream factory (uses built-in if null)
     *
     * @throws InvalidArgumentException When hosted card data is invalid
     */
    public function __construct(
        HostedCard|array $hostedCard,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
    ) {
        // Normalize to HostedCard object
        $this->hostedCard = match (true) {
            $hostedCard instanceof HostedCard => $hostedCard,
            is_array($hostedCard) => HostedCard::fromData($hostedCard),
        };

        $this->validate();
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

        // Serialize hosted card to JSON
        $data = $this->hostedCard->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request
        return $requestFactory
            ->createRequest('POST', '/hosted-cards')
            ->withBody($streamFactory->createStream($json));
    }

    /**
     * Gets the hosted card being created.
     *
     * @return HostedCard
     */
    public function getHostedCard(): HostedCard
    {
        return $this->hostedCard;
    }

    /**
     * Validates the hosted card data for creation.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // Card is required for creation
        if ($this->hostedCard->card === null) {
            throw new InvalidArgumentException('Card data is required to create a hosted card');
        }
    }
}
