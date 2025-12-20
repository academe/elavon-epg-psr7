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

    private readonly ManualBatch $manualBatch;

    /**
     * @param ManualBatch|array<string, mixed> $manualBatch Manual batch data or array     *
     * @throws InvalidArgumentException When manual batch data is invalid
     */
    public function __construct(
        ManualBatch|array $manualBatch
    ) {
        // Normalize to ManualBatch object
        $this->manualBatch = match (true) {
            $manualBatch instanceof ManualBatch => $manualBatch,
            is_array($manualBatch) => ManualBatch::fromData($manualBatch),
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

        // Serialize manual batch to JSON
        $data = $this->manualBatch->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request
        return $requestFactory
            ->createRequest('POST', '/manual-batches')
            ->withBody($streamFactory->createStream($json));
    }

    /**
     * Gets the manual batch being created.
     *
     * @return ManualBatch
     */
    public function getManualBatch(): ManualBatch
    {
        return $this->manualBatch;
    }
}
