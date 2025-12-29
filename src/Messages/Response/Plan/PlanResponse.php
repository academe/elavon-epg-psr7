<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\Plan;

use Academe\Elavon\Epg\Psr7\Contracts\ResponseMessage;
use Academe\Elavon\Epg\Psr7\Dtos\Plan;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

/**
 * Plan Response.
 *
 * Parses a PSR-7 response from the EPG API containing either plan data or error details.
 *
 * For successful responses (2xx), contains plan data.
 * For error responses (4xx, 5xx), contains error details.
 */
class PlanResponse implements ResponseMessage
{
    use ParsesPsr7Response;

    public readonly ?Plan $plan;

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
            $this->plan = Plan::fromData($data);
            $this->error = null;
        } else {
            $this->plan = null;
            $this->error = self::parseErrorData($data);
        }
    }
}
