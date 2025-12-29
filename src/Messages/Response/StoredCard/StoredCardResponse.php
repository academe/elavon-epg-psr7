<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\StoredCard;

use Academe\Elavon\Epg\Psr7\Contracts\ResponseMessage;
use Academe\Elavon\Epg\Psr7\Dtos\StoredCard;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

/**
 * Stored Card Response.
 *
 * Parses PSR-7 responses for stored card operations (create, retrieve, update).
 *
 * Example usage:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Response\StoredCardResponse;
 *
 * // Parse response from API
 * $response = StoredCardResponse::fromPsr7Response($psrResponse);
 *
 * if ($response->isSuccessful()) {
 *     $storedCard = $response->getStoredCard();
 *     echo "Stored card ID: " . $storedCard->id;
 *     echo "Shopper: " . $storedCard->shopper;
 * } else {
 *     $error = $response->getError();
 *     echo "Error: " . $error->message;
 * }
 * ```
 */
class StoredCardResponse implements ResponseMessage
{
    use ParsesPsr7Response;

    public readonly ?StoredCard $storedCard;

    /**
     * @param array<string, mixed> $data Parsed response body data
     * @param int $statusCode HTTP status code
     */
    public function __construct(array $data, int $statusCode)
    {
        $this->statusCode = $statusCode;

        // Parse response based on status code
        if ($this->isSuccessful()) {
            $this->storedCard = StoredCard::fromData($data);
            $this->error = null;
        } else {
            $this->storedCard = null;
            $this->error = self::parseErrorData($data);
        }
    }
}
