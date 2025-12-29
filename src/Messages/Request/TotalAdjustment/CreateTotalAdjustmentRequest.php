<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\TotalAdjustment;

use Academe\Elavon\Epg\Psr7\Contracts\RequestMessage;
use Academe\Elavon\Epg\Psr7\Dtos\TotalAdjustment;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

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
 * Use the ElavonApiFactory to add these.
 */
class CreateTotalAdjustmentRequest implements RequestMessage
{
    use HasPsr17Factories;

    /**
     * @param TotalAdjustment $totalAdjustment Total adjustment data
     */
    public function __construct(
        public readonly TotalAdjustment $totalAdjustment
    ) {
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{totalAdjustment: TotalAdjustment|array<string, mixed>} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('totalAdjustment', $data)) {
            throw new InvalidArgumentException("Missing required key 'totalAdjustment' in data");
        }

        $totalAdjustment = $data['totalAdjustment'] instanceof TotalAdjustment
            ? $data['totalAdjustment']
            : TotalAdjustment::fromData($data['totalAdjustment']);

        return new static($totalAdjustment);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Use built-in factories if none provided

        // Serialize total adjustment to JSON
        $data = $this->totalAdjustment->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request
        return $this->getRequestFactory()
            ->createRequest('POST', '/total-adjustments')
            ->withBody($this->getStreamFactory()->createStream($json));
    }
}
