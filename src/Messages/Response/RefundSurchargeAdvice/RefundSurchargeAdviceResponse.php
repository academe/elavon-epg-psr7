<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\RefundSurchargeAdvice;

use Academe\Elavon\Epg\Psr7\Dtos\ErrorResponse;
use Academe\Elavon\Epg\Psr7\Dtos\RefundSurchargeAdvice;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
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
    use HandlesErrors;

    private readonly ?RefundSurchargeAdvice $refundSurchargeAdvice;

    /**
     * @param ResponseInterface $response PSR-7 response from refund surcharge advice operation
     */
    public function __construct(
        private readonly ResponseInterface $response,
    ) {
        // Parse response based on status code
        if ($this->isSuccessful()) {
            $this->refundSurchargeAdvice = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->refundSurchargeAdvice = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    /**
     * Creates instance from PSR-7 response.
     *
     * @param ResponseInterface $response
     * @return self
     */
    public static function fromPsr7Response(ResponseInterface $response): self
    {
        return new self($response);
    }

    /**
     * Gets the refund surcharge advice data (null if error response).
     *
     * @return RefundSurchargeAdvice|null
     */
    public function getRefundSurchargeAdvice(): ?RefundSurchargeAdvice
    {
        return $this->refundSurchargeAdvice;
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
     * Gets the underlying PSR-7 response.
     *
     * @return ResponseInterface
     */
    public function getPsr7Response(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Parses successful response body into RefundSurchargeAdvice DTO.
     *
     * @return RefundSurchargeAdvice
     * @throws InvalidArgumentException
     */
    private function parseSuccessResponse(): RefundSurchargeAdvice
    {
        $data = $this->parseJsonBody();
        return RefundSurchargeAdvice::fromData($data);
    }

}
