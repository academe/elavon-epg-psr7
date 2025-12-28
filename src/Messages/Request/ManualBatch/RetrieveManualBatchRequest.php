<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\ManualBatch;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Retrieve Manual Batch Request.
 *
 * Builds a PSR-7 request for retrieving a specific manual batch (GET /manual-batches/{id}).
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\ManualBatch\RetrieveManualBatchRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 *
 * // Build the base request
 * $request = (new RetrieveManualBatchRequest('mb123'))->build();
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
class RetrieveManualBatchRequest
{
    use HasPsr17Factories;

    /**
     * @param string $manualBatchId Manual batch ID to retrieve     *
     * @throws InvalidArgumentException When manual batch ID is empty
     */
    public function __construct(
        public readonly string $manualBatchId
    ) {
        if (empty($this->manualBatchId)) {
            throw new InvalidArgumentException('Manual batch ID cannot be empty');
        }
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{manualBatchId: string} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('manualBatchId', $data)) {
            throw new InvalidArgumentException("Missing required key 'manualBatchId' in data");
        }

        return new static($data['manualBatchId']);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Build PSR-7 GET request
        return $this->getRequestFactory()
            ->createRequest('GET', '/manual-batches/' . $this->manualBatchId);
    }
}
