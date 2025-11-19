<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\StoredAchPayment;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

/**
 * Delete Stored ACH Payment Response.
 *
 * Parses a PSR-7 response from the EPG API for stored ACH payment deletion
 * (DELETE /stored-ach-payments/{id}).
 *
 * For successful deletions (204 No Content), the response body is empty.
 * For error responses (4xx, 5xx), contains error details.
 */
class DeleteStoredAchPaymentResponse
{
    use HandlesErrors;

    private readonly bool $deleted;

    /**
     * @param ResponseInterface $response PSR-7 response from the API
     *
     * @throws InvalidArgumentException When response cannot be parsed
     */
    public function __construct(
        private readonly ResponseInterface $response,
    ) {
        // Parse response based on status code
        if ($this->isSuccessful()) {
            // 204 No Content indicates successful deletion
            $this->deleted = $this->response->getStatusCode() === 204;
            $this->error = null;
        } else {
            $this->deleted = false;
            $this->error = $this->parseErrorResponse();
        }
    }

    /**
     * Creates a DeleteStoredAchPaymentResponse from a PSR-7 response.
     *
     * @param ResponseInterface $response PSR-7 response
     *
     * @return self
     * @throws InvalidArgumentException When response cannot be parsed
     */
    public static function fromPsr7Response(ResponseInterface $response): self
    {
        return new self($response);
    }

    /**
     * Checks if the stored ACH payment was successfully deleted.
     *
     * @return bool True if deleted (204 status), false otherwise
     */
    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * Gets the HTTP status code.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    /**
     * Gets the original PSR-7 response.
     *
     * @return ResponseInterface
     */
    public function getPsr7Response(): ResponseInterface
    {
        return $this->response;
    }
}
