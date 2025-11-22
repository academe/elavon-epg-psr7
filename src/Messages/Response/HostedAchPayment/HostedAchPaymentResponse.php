<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\HostedAchPayment;

use Academe\Elavon\Epg\Psr7\Dtos\HostedAchPayment;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

/**
 * Hosted ACH Payment Response.
 *
 * Parses PSR-7 responses for hosted ACH payment operations (create, retrieve).
 *
 * Example usage:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Response\HostedAchPayment\HostedAchPaymentResponse;
 *
 * // Parse response from API
 * $response = HostedAchPaymentResponse::fromPsr7Response($psrResponse);
 *
 * if ($response->isSuccessful()) {
 *     $hostedAchPayment = $response->getHostedAchPayment();
 *     echo "Hosted ACH payment ID: " . $hostedAchPayment->id;
 *     echo "Expires at: " . $hostedAchPayment->expiresAt;
 * } else {
 *     $error = $response->getError();
 *     echo "Error: " . $error->message;
 * }
 * ```
 */
class HostedAchPaymentResponse
{
    use HandlesErrors;

    private readonly ?HostedAchPayment $hostedAchPayment;

    /**
     * @param ResponseInterface $response PSR-7 HTTP response
     */
    public function __construct(private readonly ResponseInterface $response)
    {
        // Parse response based on status code
        if ($this->isSuccessful()) {
            $this->hostedAchPayment = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->hostedAchPayment = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    /**
     * Creates a HostedAchPaymentResponse from a PSR-7 response.
     *
     * @param ResponseInterface $response PSR-7 HTTP response
     * @return static
     */
    public static function fromPsr7Response(ResponseInterface $response): static
    {
        return new static($response);
    }

    /**
     * Gets the hosted ACH payment from a successful response.
     *
     * @return HostedAchPayment|null Hosted ACH payment on success, null on error
     */
    public function getHostedAchPayment(): ?HostedAchPayment
    {
        return $this->hostedAchPayment;
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
     * Parses a successful response into a HostedAchPayment object.
     *
     * @return HostedAchPayment
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseSuccessResponse(): HostedAchPayment
    {
        $data = $this->parseJsonBody();
        return HostedAchPayment::fromData($data);
    }

}
