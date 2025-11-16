<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\Concerns;

use Academe\Elavon\Epg\Psr7\DataObjects\ErrorResponse;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

/**
 * Error Handling for Response Messages.
 *
 * Provides error detection and parsing for PSR-7 response messages.
 * Responses using this trait can handle both success and error responses.
 */
trait HandlesErrors
{
    private readonly ?ErrorResponse $error;

    /**
     * Checks if the response has an error.
     *
     * @return bool
     */
    public function hasError(): bool
    {
        return $this->error !== null;
    }

    /**
     * Gets the error response details.
     *
     * Only available for error responses (4xx, 5xx status codes).
     *
     * @return ErrorResponse|null Returns null if response was successful
     */
    public function getError(): ?ErrorResponse
    {
        return $this->error;
    }

    /**
     * Checks if the response was successful (2xx status code).
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        $statusCode = $this->getStatusCode();
        return $statusCode >= 200 && $statusCode < 300;
    }

    /**
     * Parses an error response into an ErrorResponse object.
     *
     * @return ErrorResponse
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseErrorResponse(): ErrorResponse
    {
        $data = $this->parseJsonBody();
        return ErrorResponse::fromArray($data);
    }

    /**
     * Parses the JSON response body.
     *
     * Must be implemented by the using class.
     *
     * @return array<string, mixed>
     * @throws InvalidArgumentException When JSON is invalid
     */
    abstract private function parseJsonBody(): array;

    /**
     * Gets the HTTP status code.
     *
     * Must be implemented by the using class.
     *
     * @return int
     */
    abstract public function getStatusCode(): int;
}
