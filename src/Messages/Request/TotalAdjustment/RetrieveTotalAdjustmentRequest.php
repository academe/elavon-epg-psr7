<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\TotalAdjustment;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Retrieve Total Adjustment Request.
 *
 * Builds a PSR-7 request for retrieving a single total adjustment (GET /total-adjustments/{id}).
 *
 * Note: This class builds the base request but does NOT add:
 * - Elavon API headers (Accept, Accept-Version)
 * - Environment configuration (sandbox, production, custom base URI)
 * - Authentication headers (Authorization)
 * Use the ElavonApiRequest decorator to add these via fluent interface.
 */
class RetrieveTotalAdjustmentRequest
{
    /**
     * @param string $totalAdjustmentId Total adjustment ID to retrieve
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     * @param string $baseUri Base URI for the API (e.g., "https://api.eu.elavonpayments.com")
     *
     * @throws InvalidArgumentException When total adjustment ID is empty
     */
    public function __construct(
        private readonly string $totalAdjustmentId,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly string $baseUri = 'https://api.eu.elavonpayments.com',
    ) {
        if (empty($this->totalAdjustmentId)) {
            throw new InvalidArgumentException('Total adjustment ID cannot be empty');
        }
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Use built-in factory if none provided
        $requestFactory = $this->requestFactory ?? new Psr17Factory();

        // Build PSR-7 GET request
        return $requestFactory
            ->createRequest('GET', $this->baseUri . '/total-adjustments/' . $this->totalAdjustmentId)
            ->withHeader('Accept', 'application/json');
    }

    /**
     * Gets the total adjustment ID being retrieved.
     *
     * @return string
     */
    public function getTotalAdjustmentId(): string
    {
        return $this->totalAdjustmentId;
    }
}
