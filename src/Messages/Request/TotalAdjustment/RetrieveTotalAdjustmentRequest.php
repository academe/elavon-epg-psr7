<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\TotalAdjustment;

use Academe\Elavon\Epg\Psr7\Contracts\RequestMessage;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Retrieve Total Adjustment Request.
 *
 * Builds a PSR-7 request for retrieving a single total adjustment (GET /total-adjustments/{id}).
 *
 * Note: This class builds the base request but does NOT add:
 * - Elavon API headers (Accept, Accept-Version)
 * - Environment configuration (sandbox, production, custom base URI)
 * - Authentication headers (Authorization)
 * Use the ElavonApiFactory to add these.
 */
class RetrieveTotalAdjustmentRequest implements RequestMessage
{
    use HasPsr17Factories;

    /**
     * @param string $totalAdjustmentId Total adjustment ID to retrieve
     *
     * @throws InvalidArgumentException When total adjustment ID is empty
     */
    public function __construct(
        public readonly string $totalAdjustmentId
    ) {
        if (empty($this->totalAdjustmentId)) {
            throw new InvalidArgumentException('Total adjustment ID cannot be empty');
        }
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{totalAdjustmentId: string} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('totalAdjustmentId', $data)) {
            throw new InvalidArgumentException("Missing required key 'totalAdjustmentId' in data");
        }

        return new static($data['totalAdjustmentId']);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Use built-in factory if none provided

        // Build PSR-7 GET request
        return $this->getRequestFactory()
            ->createRequest('GET', '/total-adjustments/' . $this->totalAdjustmentId);
    }
}
