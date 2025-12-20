<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\StoredAchPayment;

use Academe\Elavon\Epg\Psr7\Dtos\StoredAchPayment;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
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
class UpdateStoredAchPaymentRequest
{
    use HasPsr17Factories;

    private readonly StoredAchPayment $updates;

    /**
     * @param string $storedAchPaymentId Stored ACH payment ID to update
     * @param StoredAchPayment|array<string, mixed> $updates Update data (partial stored ACH payment)     *
     * @throws InvalidArgumentException When stored ACH payment ID is empty or updates are invalid
     */
    public function __construct(
        private readonly string $storedAchPaymentId,
        StoredAchPayment|array $updates
    ) {
        if (empty($this->storedAchPaymentId)) {
            throw new InvalidArgumentException('Stored ACH payment ID cannot be empty');
        }

        // Normalize to StoredAchPayment object
        $this->updates = match (true) {
            $updates instanceof StoredAchPayment => $updates,
            is_array($updates) => StoredAchPayment::fromData($updates),
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
        $requestFactory = $this->getRequestFactory();
        $streamFactory = $this->getStreamFactory();

        // Serialize updates to JSON
        $data = $this->updates->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request (API uses POST for updates)
        return $requestFactory
            ->createRequest('POST', '/stored-ach-payments/' . $this->storedAchPaymentId)
            ->withBody($streamFactory->createStream($json));
    }

    /**
     * Gets the stored ACH payment ID being updated.
     *
     * @return string
     */
    public function getStoredAchPaymentId(): string
    {
        return $this->storedAchPaymentId;
    }

    /**
     * Gets the update data.
     *
     * @return StoredAchPayment
     */
    public function getUpdates(): StoredAchPayment
    {
        return $this->updates;
    }
}
