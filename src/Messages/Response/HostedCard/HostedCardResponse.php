<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\HostedCard;

use Academe\Elavon\Epg\Psr7\Contracts\ResponseMessage;
use Academe\Elavon\Epg\Psr7\Dtos\HostedCard;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

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
class HostedCardResponse implements ResponseMessage
{
    use ParsesPsr7Response;

    public readonly ?HostedCard $hostedCard;

    /**
     * @param array<string, mixed> $data Parsed response body data
     * @param int $statusCode HTTP status code
     */
    public function __construct(array $data, int $statusCode)
    {
        $this->statusCode = $statusCode;

        // Parse response based on status code
        if ($this->isSuccessful()) {
            $this->hostedCard = HostedCard::fromData($data);
            $this->error = null;
        } else {
            $this->hostedCard = null;
            $this->error = self::parseErrorData($data);
        }
    }
}
