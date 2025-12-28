<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\StoredCard;

use Academe\Elavon\Epg\Psr7\Dtos\StoredCard;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Update Stored Card Request.
 *
 * Builds a PSR-7 request for updating a stored card (PATCH /stored-cards/{id}).
 *
 * This supports partial updates - only the fields provided will be updated.
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\UpdateStoredCardRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 * use Academe\Elavon\Epg\Psr7\Dtos\StoredCard;
 *
 * // Build the updates
 * $updates = new StoredCard(
 *     customReference: 'new-reference-123',
 *     customFields: ['status' => 'active'],
 * );
 *
 * // Build the request
 * $request = (new UpdateStoredCardRequest('sc123', $updates))->build();
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
class UpdateStoredCardRequest
{
    use HasPsr17Factories;

    /**
     * @param string $storedCardId Stored card ID to update
     * @param StoredCard $storedCard Update data (partial stored card)     *
     * @throws InvalidArgumentException When stored card ID is empty or updates are invalid
     */
    public function __construct(
        public readonly string $storedCardId,
        public readonly StoredCard $storedCard
    ) {
        if (empty($this->storedCardId)) {
            throw new InvalidArgumentException('Stored card ID cannot be empty');
        }
    }

    /**
     * @param array{storedCardId: string, storedCard: StoredCard|array<string, mixed>} $data
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('storedCardId', $data)) {
            throw new InvalidArgumentException("Missing required key 'storedCardId' in data");
        }

        if (! array_key_exists('storedCard', $data)) {
            throw new InvalidArgumentException("Missing required key 'storedCard' in data");
        }

        $storedCard = $data['storedCard'] instanceof StoredCard
            ? $data['storedCard']
            : StoredCard::fromData($data['storedCard']);

        return new static($data['storedCardId'], $storedCard);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Serialize updates to JSON
        $data = $this->storedCard->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 PATCH request
        return $this->getRequestFactory()
            ->createRequest('PATCH', '/stored-cards/' . $this->storedCardId)
            ->withBody($this->getStreamFactory()->createStream($json));
    }
}
