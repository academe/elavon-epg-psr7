<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\ManualBatch;

use Academe\Elavon\Epg\Psr7\Dtos\ManualBatch;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Update Manual Batch Request.
 *
 * Builds a PSR-7 request for updating a manual batch (POST /manual-batches/{id}).
 *
 * Note: The API uses POST for updates, not PATCH, per the OpenAPI specification.
 *
 * Example usage with ElavonApiRequest decorator:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\ManualBatch\UpdateManualBatchRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiRequest;
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
class UpdateManualBatchRequest
{
    private readonly ManualBatch $updates;

    /**
     * @param string $manualBatchId Manual batch ID to update
     * @param ManualBatch|array<string, mixed> $updates Update data (partial manual batch)
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     * @param StreamFactoryInterface|null $streamFactory PSR-17 stream factory (uses built-in if null)
     *
     * @throws InvalidArgumentException When manual batch ID is empty or updates are invalid
     */
    public function __construct(
        private readonly string $manualBatchId,
        ManualBatch|array $updates,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
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
        $requestFactory = $this->requestFactory ?? new Psr17Factory();
        $streamFactory = $this->streamFactory ?? new Psr17Factory();

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
