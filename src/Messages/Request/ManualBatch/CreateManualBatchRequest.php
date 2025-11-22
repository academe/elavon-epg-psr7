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
 * Create Manual Batch Request.
 *
 * Builds a PSR-7 request for creating a manual batch (POST /manual-batches).
 *
 * Manual batches allow merchants to manually control settlement batches for transactions.
 * Unlike regular batches, manual batches support create and update operations.
 *
 * Example usage with ElavonApiRequest decorator:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\ManualBatch\CreateManualBatchRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiRequest;
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
class CreateManualBatchRequest
{
    private readonly ManualBatch $manualBatch;

    /**
     * @param ManualBatch|array<string, mixed> $manualBatch Manual batch data or array
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     * @param StreamFactoryInterface|null $streamFactory PSR-17 stream factory (uses built-in if null)
     *
     * @throws InvalidArgumentException When manual batch data is invalid
     */
    public function __construct(
        ManualBatch|array $manualBatch,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
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
        $requestFactory = $this->requestFactory ?? new Psr17Factory();
        $streamFactory = $this->streamFactory ?? new Psr17Factory();

        // Serialize manual batch to JSON
        $data = $this->manualBatch->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request
        return $requestFactory
            ->createRequest('POST', '/manual-batches')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
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
