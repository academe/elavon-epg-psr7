<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\ManualBatch;

use Academe\Elavon\Epg\Psr7\Contracts\RequestMessage;
use Academe\Elavon\Epg\Psr7\Dtos\ManualBatch;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
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
class UpdateManualBatchRequest implements RequestMessage
{
    use HasPsr17Factories;

    /**
     * @param string $manualBatchId Manual batch ID to update
     * @param ManualBatch $manualBatch Update data (partial manual batch)     *
     * @throws InvalidArgumentException When manual batch ID is empty
     */
    public function __construct(
        public readonly string $manualBatchId,
        public readonly ManualBatch $manualBatch
    ) {
        if (empty($this->manualBatchId)) {
            throw new InvalidArgumentException('Manual batch ID cannot be empty');
        }
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{manualBatchId: string, manualBatch: ManualBatch|array<string, mixed>} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('manualBatchId', $data)) {
            throw new InvalidArgumentException("Missing required key 'manualBatchId' in data");
        }

        if (! array_key_exists('manualBatch', $data)) {
            throw new InvalidArgumentException("Missing required key 'manualBatch' in data");
        }

        $manualBatch = $data['manualBatch'] instanceof ManualBatch
            ? $data['manualBatch']
            : ManualBatch::fromData($data['manualBatch']);

        return new static($data['manualBatchId'], $manualBatch);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Serialize updates to JSON
        $data = $this->manualBatch->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request (API uses POST for updates)
        return $this->getRequestFactory()
            ->createRequest('POST', '/manual-batches/' . $this->manualBatchId)
            ->withBody($this->getStreamFactory()->createStream($json));
    }
}
