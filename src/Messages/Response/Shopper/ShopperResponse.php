<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\Shopper;

use Academe\Elavon\Epg\Psr7\Dtos\Shopper;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

/**
 * Shopper Response.
 *
 * Parses PSR-7 responses for stored card operations (create, retrieve, update).
 *
 * Example usage:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Response\ShopperResponse;
 *
 * // Parse response from API
 * $response = ShopperResponse::fromPsr7Response($psrResponse);
 *
 * if ($response->isSuccessful()) {
 *     $storedCard = $response->getShopper();
 *     echo "shopper ID: " . $storedCard->id;
 *     echo "Shopper: " . $storedCard->shopper;
 * } else {
 *     $error = $response->getError();
 *     echo "Error: " . $error->message;
 * }
 * ```
 */
class ShopperResponse
{
    use HandlesErrors;

    private readonly ?Shopper $storedCard;

    /**
     * @param ResponseInterface $response PSR-7 HTTP response
     */
    public function __construct(private readonly ResponseInterface $response)
    {
        // Parse response based on status code
        if ($this->isSuccessful()) {
            $this->storedCard = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->storedCard = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    /**
     * Creates a ShopperResponse from a PSR-7 response.
     *
     * @param ResponseInterface $response PSR-7 HTTP response
     * @return static
     */
    public static function fromPsr7Response(ResponseInterface $response): static
    {
        return new static($response);
    }

    /**
     * Gets the stored card from a successful response.
     *
     * @return Shopper|null shopper on success, null on error
     */
    public function getShopper(): ?Shopper
    {
        return $this->storedCard;
    }

    /**
     * Gets the PSR-7 response.
     *
     * @return ResponseInterface
     */
    public function getPsr7Response(): ResponseInterface
    {
        return $this->response;
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
     * Parses a successful response into a Shopper object.
     *
     * @return Shopper
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseSuccessResponse(): Shopper
    {
        $data = $this->parseJsonBody();
        return Shopper::fromData($data);
    }

}
