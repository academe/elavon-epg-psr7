<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\StoredAchPayment;

use Academe\Elavon\Epg\Psr7\Dtos\StoredAchPayment;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

/**
 * Stored ACH Payment Response.
 *
 * Parses PSR-7 responses for stored ACH payment operations (create, retrieve, update).
 *
 * Example usage:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Response\StoredAchPayment\StoredAchPaymentResponse;
 *
 * // Parse response from API
 * $response = StoredAchPaymentResponse::fromPsr7Response($psrResponse);
 *
 * if ($response->isSuccessful()) {
 *     $storedAchPayment = $response->getStoredAchPayment();
 *     echo "Stored ACH payment ID: " . $storedAchPayment->id;
 *     echo "Shopper: " . $storedAchPayment->shopper;
 * } else {
 *     $error = $response->getError();
 *     echo "Error: " . $error->message;
 * }
 * ```
 */
class StoredAchPaymentResponse
{
    use HandlesErrors;

    private readonly ?StoredAchPayment $storedAchPayment;

    /**
     * @param ResponseInterface $response PSR-7 HTTP response
     */
    public function __construct(private readonly ResponseInterface $response)
    {
        // Parse response based on status code
        if ($this->isSuccessful()) {
            $this->storedAchPayment = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->storedAchPayment = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    /**
     * Creates a StoredAchPaymentResponse from a PSR-7 response.
     *
     * @param ResponseInterface $response PSR-7 HTTP response
     * @return static
     */
    public static function fromPsr7Response(ResponseInterface $response): static
    {
        return new static($response);
    }

    /**
     * Gets the stored ACH payment from a successful response.
     *
     * @return StoredAchPayment|null Stored ACH payment on success, null on error
     */
    public function getStoredAchPayment(): ?StoredAchPayment
    {
        return $this->storedAchPayment;
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
     * Parses a successful response into a StoredAchPayment object.
     *
     * @return StoredAchPayment
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseSuccessResponse(): StoredAchPayment
    {
        $data = $this->parseJsonBody();
        return StoredAchPayment::fromData($data);
    }

}
