<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\StoredAchPayment;

use Academe\Elavon\Epg\Psr7\Dtos\StoredAchPayment;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

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
    use ParsesPsr7Response;

    public readonly ?StoredAchPayment $storedAchPayment;

    /**
     * @param array<string, mixed> $data Parsed response body data
     * @param int $statusCode HTTP status code
     */
    public function __construct(array $data, int $statusCode)
    {
        $this->statusCode = $statusCode;

        // Parse response based on status code
        if ($this->isSuccessful()) {
            $this->storedAchPayment = StoredAchPayment::fromData($data);
            $this->error = null;
        } else {
            $this->storedAchPayment = null;
            $this->error = self::parseErrorData($data);
        }
    }
}
