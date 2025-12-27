<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\HostedAchPayment;

use Academe\Elavon\Epg\Psr7\Dtos\HostedAchPayment;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

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
    use ParsesPsr7Response;

    public readonly ?HostedAchPayment $hostedAchPayment;

    /**
     * @param array<string, mixed> $data Parsed response body data
     * @param int $statusCode HTTP status code
     */
    public function __construct(array $data, int $statusCode)
    {
        $this->statusCode = $statusCode;

        // Parse response based on status code
        if ($this->isSuccessful()) {
            $this->hostedAchPayment = HostedAchPayment::fromData($data);
            $this->error = null;
        } else {
            $this->hostedAchPayment = null;
            $this->error = self::parseErrorData($data);
        }
    }
}
