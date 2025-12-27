<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\Plan;

use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;
use Psr\Http\Message\ResponseInterface;

/**
 * Delete Plan Response.
 *
 * Parses a PSR-7 response from the EPG API for plan deletion (DELETE /plans/{id}).
 *
 * For successful deletions (204 No Content), the response body is empty.
 * For error responses (4xx, 5xx), contains error details.
 */
class DeletePlanResponse
{
    use ParsesPsr7Response;

    public readonly bool $deleted;

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
            // 204 No Content indicates successful deletion
            $this->deleted = $this->statusCode === 204;
            $this->error = null;
        } else {
            $this->deleted = false;
            $this->error = self::parseErrorData($data);
        }
    }
    /**
     * Checks if the plan was successfully deleted.
     *
     * @return bool True if deleted (204 status), false otherwise
     */
    public function isDeleted(): bool
    {
        return $this->deleted;
    }
    /**
     * Gets the original PSR-7 response.
     *
     * @return ResponseInterface
     */
}
