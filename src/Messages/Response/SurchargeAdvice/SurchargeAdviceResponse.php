<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\SurchargeAdvice;

use Academe\Elavon\Epg\Psr7\Contracts\ResponseMessage;
use Academe\Elavon\Epg\Psr7\Dtos\ErrorResponse;
use Academe\Elavon\Epg\Psr7\Dtos\SurchargeAdvice;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;
use Psr\Http\Message\ResponseInterface;

/**
 * Surcharge Advice Response.
 *
 * Parses responses from surcharge advice operations (create, retrieve).
 *
 * Example usage:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Response\SurchargeAdviceResponse;
 *
 * // Parse the PSR-7 response
 * $response = new SurchargeAdviceResponse($psrResponse);
 *
 * if ($response->isSuccessful()) {
 *     $surchargeAdvice = $response->getSurchargeAdvice();
 *     echo "Surcharge rate: " . $surchargeAdvice->surchargeRate;
 *     echo "Adjusted total: " . $surchargeAdvice->adjustedTotal->amount;
 * } else {
 *     $error = $response->getError();
 *     echo "Error: " . $error->status;
 * }
 * ```
 */
class SurchargeAdviceResponse implements ResponseMessage
{
    use ParsesPsr7Response;

    public readonly ?SurchargeAdvice $surchargeAdvice;

    /**
     * @param array<string, mixed> $data Parsed response body data
     * @param int $statusCode HTTP status code
     */
    public function __construct(array $data, int $statusCode) {
        $this->statusCode = $statusCode;

        // Parse response based on status code
        if ($this->isSuccessful()) {
            $this->surchargeAdvice = SurchargeAdvice::fromData($data);
            $this->error = null;
        } else {
            $this->surchargeAdvice = null;
            $this->error = self::parseErrorData($data);
        }
    }
    /**
     * Gets the underlying PSR-7 response.
     *
     * @return ResponseInterface
     */
    /**
     * Parses successful response body into SurchargeAdvice DTO.
     *
     * @return SurchargeAdvice
     * @throws InvalidArgumentException
     */
}
