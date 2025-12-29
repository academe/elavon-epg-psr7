<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\ManualBatch;

use Academe\Elavon\Epg\Psr7\Contracts\ResponseMessage;
use Academe\Elavon\Epg\Psr7\Dtos\ManualBatch;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

/**
 * Manual Batch Response.
 *
 * Parses PSR-7 responses for manual batch operations (create, retrieve, update).
 *
 * Example usage:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Response\ManualBatch\ManualBatchResponse;
 *
 * // Parse response from API
 * $response = ManualBatchResponse::fromPsr7Response($psrResponse);
 *
 * if ($response->isSuccessful()) {
 *     $manualBatch = $response->getManualBatch();
 *     echo "Manual batch ID: " . $manualBatch->id;
 * } else {
 *     $error = $response->getError();
 *     echo "Error: " . $error->message;
 * }
 * ```
 */
class ManualBatchResponse implements ResponseMessage
{
    use ParsesPsr7Response;

    public readonly ?ManualBatch $manualBatch;

    /**
     * @param array<string, mixed> $data Parsed response body data
     * @param int $statusCode HTTP status code
     */
    public function __construct(array $data, int $statusCode)
    {
        $this->statusCode = $statusCode;

        // Parse response based on status code
        if ($this->isSuccessful()) {
            $this->manualBatch = ManualBatch::fromData($data);
            $this->error = null;
        } else {
            $this->manualBatch = null;
            $this->error = self::parseErrorData($data);
        }
    }
}
