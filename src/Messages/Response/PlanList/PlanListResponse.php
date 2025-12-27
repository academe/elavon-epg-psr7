<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\PlanList;

use Academe\Elavon\Epg\Psr7\Dtos\PlanList;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

/**
 * PlanList Response.
 *
 * Parses a PSR-7 response from the EPG API containing either plan list data or error details.
 *
 * For successful responses (2xx), contains plan list data.
 * For error responses (4xx, 5xx), contains error details.
 */
class PlanListResponse
{
    use ParsesPsr7Response;

    public readonly ?PlanList $planList;

    /**
     * @param array<string, mixed> $data Parsed response body data
     * @param int $statusCode HTTP status code
     */
    public function __construct(array $data, int $statusCode)
    {
        $this->statusCode = $statusCode;

        if ($this->isSuccessful()) {
            $this->planList = PlanList::fromData($data);
            $this->error = null;
        } else {
            $this->planList = null;
            $this->error = self::parseErrorData($data);
        }
    }
}
