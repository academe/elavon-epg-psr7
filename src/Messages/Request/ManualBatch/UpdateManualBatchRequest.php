<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\ManualBatch;

use Academe\Elavon\Epg\Psr7\Dtos\ManualBatch;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Update Manual Batch Request.
 *
 * Builds a PSR-7 request for updating a manual batch (POST /manual-batches/{id}).
 *
 * Note: The API uses POST for updates, not PATCH, per the OpenAPI specification.
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\ManualBatch\UpdateManualBatchRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 * use Academe\Elavon\Epg\Psr7\Dtos\ManualBatch;
 *
 * // Build the updates
 * $updates = new ManualBatch(
 *     customReference: 'updated-ref-123',
 *     customFields: ['status' => 'closed'],
 * );
 *
 * // Build the request
 * $request = (new UpdateManualBatchRequest('mb123', $updates))->build();
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
class UpdateManualBatchRequest
{
    use HasPsr17Factories;

    private readonly ManualBatch $updates;

    /**
     * @param string $manualBatchId Manual batch ID to update
     * @param ManualBatch|array<string, mixed> $updates Update data (partial manual batch)     *
     * @throws InvalidArgumentException When manual batch ID is empty or updates are invalid
     */
    public function __construct(
        private readonly string $manualBatchId,
        ManualBatch|array $updates
    ) {
        if (empty($this->manualBatchId)) {
            throw new InvalidArgumentException('Manual batch ID cannot be empty');
        }

        // Normalize to ManualBatch object
        $this->updates = match (true) {
            $updates instanceof ManualBatch => $updates,
            is_array($updates) => ManualBatch::fromData($updates),
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
            ->createRequest('POST', '/manual-batches/' . $this->manualBatchId)
            ->withBody($streamFactory->createStream($json));
    }

    /**
     * Gets the manual batch ID being updated.
     *
     * @return string
     */
    public function getManualBatchId(): string
    {
        return $this->manualBatchId;
    }

    /**
     * Gets the update data.
     *
     * @return ManualBatch
     */
    public function getUpdates(): ManualBatch
    {
        return $this->updates;
    }
}
