<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\StoredCard;

use Academe\Elavon\Epg\Psr7\Dtos\StoredCard;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Create Stored Card Request.
 *
 * Builds a PSR-7 request for creating a stored card (POST /stored-cards).
 *
 * Stored cards allow merchants to charge customers for recurring payments without requiring
 * them to re-enter card details. The card can be initialized from a hosted card or provided directly.
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\CreateStoredCardRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 * use Academe\Elavon\Epg\Psr7\Dtos\StoredCard;
 *
 * // Build the stored card
 * $storedCard = new StoredCard(
 *     shopper: 'https://api.example.com/shoppers/s123',
 *     hostedCard: 'https://api.example.com/hosted-cards/hc456',
 *     customReference: 'customer-card-789',
 * );
 *
 * // Build the request
 * $request = (new CreateStoredCardRequest($storedCard))->build();
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
class CreateStoredCardRequest
{
    use HasPsr17Factories;

    /**
     * @param StoredCard $storedCard Stored card data     *
     * @throws InvalidArgumentException When stored card data is invalid
     */
    public function __construct(
        public readonly StoredCard $storedCard
    ) {
        $this->validate();
    }

    /**
     * @param array{storedCard: StoredCard|array<string, mixed>} $data
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('storedCard', $data)) {
            throw new InvalidArgumentException("Missing required key 'storedCard' in data");
        }

        $storedCard = $data['storedCard'] instanceof StoredCard
            ? $data['storedCard']
            : StoredCard::fromData($data['storedCard']);

        return new static($storedCard);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Serialize stored card to JSON
        $data = $this->storedCard->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request
        return $this->getRequestFactory()
            ->createRequest('POST', '/stored-cards')
            ->withBody($this->getStreamFactory()->createStream($json));
    }

    /**
     * Validates the stored card data for creation.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // Shopper is required for creation
        if ($this->storedCard->shopper === null) {
            throw new InvalidArgumentException('Shopper URL is required to create a stored card');
        }

        // Must have either hostedCard or card data
        if ($this->storedCard->hostedCard === null && $this->storedCard->card === null) {
            throw new InvalidArgumentException('Either hostedCard URL or card data is required to create a stored card');
        }
    }
}
