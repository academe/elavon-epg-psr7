<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\TotalAdjustment;

use Academe\Elavon\Epg\Psr7\Dtos\TotalAdjustment;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Create Total Adjustment Request.
 *
 * Builds a PSR-7 request for creating a total adjustment (POST /total-adjustments).
 *
 * Total adjustments allow modifying the total and/or tip of an existing transaction.
 *
 * Note: This class builds the base request but does NOT add:
 * - Elavon API headers (Accept, Accept-Version)
 * - Environment configuration (sandbox, production, custom base URI)
 * - Authentication headers (Authorization)
 * Use the ElavonApiRequest decorator to add these via fluent interface.
 */
class CreateTotalAdjustmentRequest
{
    private readonly TotalAdjustment $totalAdjustment;

    /**
     * @param TotalAdjustment|array<string, mixed> $totalAdjustment Total adjustment data or array
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     * @param StreamFactoryInterface|null $streamFactory PSR-17 stream factory (uses built-in if null)
     *
     * @throws InvalidArgumentException When total adjustment data is invalid
     */
    public function __construct(
        TotalAdjustment|array $totalAdjustment,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
    ) {
        // Normalize to TotalAdjustment object
        $this->totalAdjustment = match (true) {
            $totalAdjustment instanceof TotalAdjustment => $totalAdjustment,
            is_array($totalAdjustment) => TotalAdjustment::fromData($totalAdjustment),
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

        // Serialize total adjustment to JSON
        $data = $this->totalAdjustment->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request
        return $requestFactory
            ->createRequest('POST', '/total-adjustments')
            ->withBody($streamFactory->createStream($json));
    }

    /**
     * Gets the total adjustment being created.
     *
     * @return TotalAdjustment
     */
    public function getTotalAdjustment(): TotalAdjustment
    {
        return $this->totalAdjustment;
    }
}
