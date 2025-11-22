<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\Order;

use Academe\Elavon\Epg\Psr7\Dtos\Order;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

/**
 * Order Response.
 *
 * Parses a PSR-7 response from the EPG API containing either order data or error details.
 *
 * For successful responses (2xx), contains order data.
 * For error responses (4xx, 5xx), contains error details.
 */
class OrderResponse
{
    use HandlesErrors;

    private readonly ?Order $order;

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
            $this->order = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->order = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    /**
     * Creates an OrderResponse from a PSR-7 response.
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
     * Gets the parsed Order object.
     *
     * Only available for successful responses (2xx status codes).
     *
     * @return Order|null Returns null if response was an error
     */
    public function getOrder(): ?Order
    {
        return $this->order;
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
     * Parses a successful response into an Order object.
     *
     * @return Order
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseSuccessResponse(): Order
    {
        $data = $this->parseJsonBody();
        return Order::fromData($data);
    }

}
