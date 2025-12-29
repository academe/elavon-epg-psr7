<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\HostedCard;

use Academe\Elavon\Epg\Psr7\Contracts\RequestMessage;
use Academe\Elavon\Epg\Psr7\Dtos\HostedCard;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

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
class CreateHostedCardRequest implements RequestMessage
{
    use HasPsr17Factories;

    /**
     * @param HostedCard $hostedCard Hosted card data     *
     * @throws InvalidArgumentException When hosted card data is invalid
     */
    public function __construct(
        public readonly HostedCard $hostedCard
    ) {
        $this->validate();
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{hostedCard: HostedCard|array<string, mixed>} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('hostedCard', $data)) {
            throw new InvalidArgumentException("Missing required key 'hostedCard' in data");
        }

        $hostedCard = $data['hostedCard'] instanceof HostedCard
            ? $data['hostedCard']
            : HostedCard::fromData($data['hostedCard']);

        return new static($hostedCard);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Serialize hosted card to JSON
        $data = $this->hostedCard->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request
        return $this->getRequestFactory()
            ->createRequest('POST', '/hosted-cards')
            ->withBody($this->getStreamFactory()->createStream($json));
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
