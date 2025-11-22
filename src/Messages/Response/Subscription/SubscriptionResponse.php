<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\Subscription;

use Academe\Elavon\Epg\Psr7\Dtos\Subscription;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

/**
 * Subscription Response.
 *
 * Parses a PSR-7 response from the EPG API containing either subscription data or error details.
 *
 * For successful responses (2xx), contains subscription data.
 * For error responses (4xx, 5xx), contains error details.
 */
class SubscriptionResponse
{
    use HandlesErrors;

    private readonly ?Subscription $subscription;

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
            $this->subscription = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->subscription = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    /**
     * Creates a SubscriptionResponse from a PSR-7 response.
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
     * Gets the parsed Subscription object.
     *
     * Only available for successful responses (2xx status codes).
     *
     * @return Subscription|null Returns null if response was an error
     */
    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
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
     * Parses a successful response into a Subscription object.
     *
     * @return Subscription
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseSuccessResponse(): Subscription
    {
        $data = $this->parseJsonBody();
        return Subscription::fromData($data);
    }

}
