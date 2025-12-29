<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\Batch;

use Academe\Elavon\Epg\Psr7\Contracts\ResponseMessage;
use Academe\Elavon\Epg\Psr7\Dtos\Batch;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

/**
 * Batch Response.
 *
 * Parses a PSR-7 response from the EPG API containing either batch data or error details.
 *
 * For successful responses (2xx), contains batch data.
 * For error responses (4xx, 5xx), contains error details.
 */
class BatchResponse implements ResponseMessage
{
    use ParsesPsr7Response;

    public readonly ?Batch $batch;

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
            $this->batch = Batch::fromData($data);
            $this->error = null;
        } else {
            $this->batch = null;
            $this->error = self::parseErrorData($data);
        }
    }
}
