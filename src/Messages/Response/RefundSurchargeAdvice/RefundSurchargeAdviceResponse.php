<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\RefundSurchargeAdvice;

use Academe\Elavon\Epg\Psr7\Dtos\ErrorResponse;
use Academe\Elavon\Epg\Psr7\Dtos\RefundSurchargeAdvice;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;
use Psr\Http\Message\ResponseInterface;

/**
 * Refund Surcharge Advice Response.
 *
 * Parses responses from refund surcharge advice operations (create, retrieve).
 *
 * Example usage:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Response\RefundSurchargeAdviceResponse;
 *
 * // Parse the PSR-7 response
 * $response = new RefundSurchargeAdviceResponse($psrResponse);
 *
 * if ($response->isSuccessful()) {
 *     $refundSurchargeAdvice = $response->getRefundSurchargeAdvice();
 *     echo "Surcharge rate: " . $refundSurchargeAdvice->surchargeRate;
 *     echo "Adjusted total: " . $refundSurchargeAdvice->adjustedTotal->amount;
 * } else {
 *     $error = $response->getError();
 *     echo "Error: " . $error->status;
 * }
 * ```
 */
class RefundSurchargeAdviceResponse
{
    use ParsesPsr7Response;

    public readonly ?RefundSurchargeAdvice $refundSurchargeAdvice;

    /**
     * @param array<string, mixed> $data Parsed response body data
     * @param int $statusCode HTTP status code
     */
    public function __construct(array $data, int $statusCode) {
        $this->statusCode = $statusCode;

        // Parse response based on status code
        if ($this->isSuccessful()) {
            $this->refundSurchargeAdvice = RefundSurchargeAdvice::fromData($data);
            $this->error = null;
        } else {
            $this->refundSurchargeAdvice = null;
            $this->error = self::parseErrorData($data);
        }
    }
    /**
     * Gets the underlying PSR-7 response.
     *
     * @return ResponseInterface
     */
    /**
     * Parses successful response body into RefundSurchargeAdvice DTO.
     *
     * @return RefundSurchargeAdvice
     * @throws InvalidArgumentException
     */
}
