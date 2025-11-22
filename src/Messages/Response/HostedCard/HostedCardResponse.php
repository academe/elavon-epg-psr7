<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\HostedCard;

use Academe\Elavon\Epg\Psr7\Dtos\HostedCard;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

/**
 * Hosted Card Response.
 *
 * Parses PSR-7 responses for hosted card operations (create, retrieve).
 *
 * Example usage:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Response\HostedCardResponse;
 *
 * // Parse response from API
 * $response = HostedCardResponse::fromPsr7Response($psrResponse);
 *
 * if ($response->isSuccessful()) {
 *     $hostedCard = $response->getHostedCard();
 *     echo "Hosted card ID: " . $hostedCard->id;
 *     echo "Expires at: " . $hostedCard->expiresAt;
 * } else {
 *     $error = $response->getError();
 *     echo "Error: " . $error->message;
 * }
 * ```
 */
class HostedCardResponse
{
    use HandlesErrors;

    private readonly ?HostedCard $hostedCard;

    /**
     * @param ResponseInterface $response PSR-7 HTTP response
     */
    public function __construct(private readonly ResponseInterface $response)
    {
        // Parse response based on status code
        if ($this->isSuccessful()) {
            $this->hostedCard = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->hostedCard = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    /**
     * Creates a HostedCardResponse from a PSR-7 response.
     *
     * @param ResponseInterface $response PSR-7 HTTP response
     * @return static
     */
    public static function fromPsr7Response(ResponseInterface $response): static
    {
        return new static($response);
    }

    /**
     * Gets the hosted card from a successful response.
     *
     * @return HostedCard|null Hosted card on success, null on error
     */
    public function getHostedCard(): ?HostedCard
    {
        return $this->hostedCard;
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
     * Parses a successful response into a HostedCard object.
     *
     * @return HostedCard
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseSuccessResponse(): HostedCard
    {
        $data = $this->parseJsonBody();
        return HostedCard::fromData($data);
    }

}
