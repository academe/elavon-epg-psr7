<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\TotalAdjustment;

use Academe\Elavon\Epg\Psr7\Dtos\TotalAdjustment;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

/**
 * Total Adjustment Response.
 *
 * Parses a PSR-7 response from the EPG API containing either total adjustment data or error details.
 *
 * For successful responses (2xx, 201), contains total adjustment data.
 * For error responses (4xx, 5xx), contains error details.
 *
 * Used for both:
 * - POST /total-adjustments (201 Created)
 * - GET /total-adjustments/{id} (200 OK)
 */
class TotalAdjustmentResponse
{
    use ParsesPsr7Response;

    public readonly ?TotalAdjustment $totalAdjustment;

    /**
     * @param array<string, mixed> $data Parsed response body data
     * @param int $statusCode HTTP status code
     *
     * @throws InvalidArgumentException When response cannot be parsed
     */
    public function __construct(array $data, int $statusCode) {
        $this->statusCode = $statusCode;

        // Parse response based on status code
        if ($this->isSuccessful()) {
            $this->totalAdjustment = TotalAdjustment::fromData($data);
            $this->error = null;
        } else {
            $this->totalAdjustment = null;
            $this->error = self::parseErrorData($data);
        }
    }
}
