<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\StoredAchPayment;

use Academe\Elavon\Epg\Psr7\Contracts\RequestMessage;
use Academe\Elavon\Epg\Psr7\Dtos\StoredAchPayment;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Update Stored ACH Payment Request.
 *
 * Builds a PSR-7 request for updating a stored ACH payment (POST /stored-ach-payments/{id}).
 *
 * Note: Only accountName can be updated according to the API specification.
 * The API uses POST for updates, not PATCH.
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\StoredAchPayment\UpdateStoredAchPaymentRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 * use Academe\Elavon\Epg\Psr7\Dtos\StoredAchPayment;
 * use Academe\Elavon\Epg\Psr7\Dtos\AchPayment;
 * use Academe\Elavon\Epg\Psr7\Enums\AchAccountType;
 *
 * // Build the updates (only accountName can be updated)
 * $updates = new StoredAchPayment(
 *     achPayment: new AchPayment(
 *         achAccountType: AchAccountType::CHECKING_PERSONAL,
 *         accountName: 'Updated Account Name',
 *     ),
 *     customReference: 'updated-ref-123',
 * );
 *
 * // Build the request
 * $request = (new UpdateStoredAchPaymentRequest('sap123', $updates))->build();
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
class UpdateStoredAchPaymentRequest implements RequestMessage
{
    use HasPsr17Factories;

    /**
     * @param string $storedAchPaymentId Stored ACH payment ID to update
     * @param StoredAchPayment $storedAchPayment Update data (partial stored ACH payment)     *
     * @throws InvalidArgumentException When stored ACH payment ID is empty or updates are invalid
     */
    public function __construct(
        public readonly string $storedAchPaymentId,
        public readonly StoredAchPayment $storedAchPayment
    ) {
        if (empty($this->storedAchPaymentId)) {
            throw new InvalidArgumentException('Stored ACH payment ID cannot be empty');
        }
    }

    /**
     * @param array{storedAchPaymentId: string, storedAchPayment: StoredAchPayment|array<string, mixed>} $data
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('storedAchPaymentId', $data)) {
            throw new InvalidArgumentException("Missing required key 'storedAchPaymentId' in data");
        }

        if (! array_key_exists('storedAchPayment', $data)) {
            throw new InvalidArgumentException("Missing required key 'storedAchPayment' in data");
        }

        $storedAchPayment = $data['storedAchPayment'] instanceof StoredAchPayment
            ? $data['storedAchPayment']
            : StoredAchPayment::fromData($data['storedAchPayment']);

        return new static($data['storedAchPaymentId'], $storedAchPayment);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Serialize updates to JSON
        $data = $this->storedAchPayment->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request (API uses POST for updates)
        return $this->getRequestFactory()
            ->createRequest('POST', '/stored-ach-payments/' . $this->storedAchPaymentId)
            ->withBody($this->getStreamFactory()->createStream($json));
    }
}
