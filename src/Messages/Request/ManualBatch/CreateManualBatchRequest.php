<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\ManualBatch;

use Academe\Elavon\Epg\Psr7\Dtos\ManualBatch;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Create Manual Batch Request.
 *
 * Builds a PSR-7 request for creating a manual batch (POST /manual-batches).
 *
 * Manual batches allow merchants to manually control settlement batches for transactions.
 * Unlike regular batches, manual batches support create and update operations.
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\ManualBatch\CreateManualBatchRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 * use Academe\Elavon\Epg\Psr7\Dtos\ManualBatch;
 *
 * // Build the manual batch
 * $manualBatch = new ManualBatch(
 *     customReference: 'batch-2024-01',
 *     customFields: ['purpose' => 'daily-settlement'],
 * );
 *
 * // Build the request
 * $request = (new CreateManualBatchRequest($manualBatch))->build();
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
class CreateManualBatchRequest
{
    use HasPsr17Factories;

    /**
     * @param ManualBatch $manualBatch Manual batch data     */
    public function __construct(
        public readonly ManualBatch $manualBatch
    ) {
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{manualBatch: ManualBatch|array<string, mixed>} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('manualBatch', $data)) {
            throw new InvalidArgumentException("Missing required key 'manualBatch' in data");
        }

        $manualBatch = $data['manualBatch'] instanceof ManualBatch
            ? $data['manualBatch']
            : ManualBatch::fromData($data['manualBatch']);

        return new static($manualBatch);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Serialize manual batch to JSON
        $data = $this->manualBatch->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request
        return $this->getRequestFactory()
            ->createRequest('POST', '/manual-batches')
            ->withBody($this->getStreamFactory()->createStream($json));
    }
}
