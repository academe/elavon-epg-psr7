<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\Notification;

use Academe\Elavon\Epg\Psr7\Dtos\Notification;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

/**
 * Notification Response.
 *
 * Parses a PSR-7 response from the EPG API containing either notification data or error details.
 *
 * For successful responses (2xx), contains notification data.
 * For error responses (4xx, 5xx), contains error details.
 */
class NotificationResponse
{
    use HandlesErrors;

    private readonly ?Notification $notification;

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
            $this->notification = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->notification = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    /**
     * Creates a NotificationResponse from a PSR-7 response.
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
     * Gets the parsed Notification object.
     *
     * Only available for successful responses (2xx status codes).
     *
     * @return Notification|null Returns null if response was an error
     */
    public function getNotification(): ?Notification
    {
        return $this->notification;
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

    /**
     * Parses a successful response into a Notification object.
     *
     * @return Notification
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseSuccessResponse(): Notification
    {
        $data = $this->parseJsonBody();
        return Notification::fromData($data);
    }

}
