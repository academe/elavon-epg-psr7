<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\SurchargeAdvice;

use Academe\Elavon\Epg\Psr7\Dtos\ErrorResponse;
use Academe\Elavon\Epg\Psr7\Dtos\SurchargeAdvice;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
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
class SurchargeAdviceResponse
{
    use HandlesErrors;

    private readonly ?SurchargeAdvice $surchargeAdvice;

    /**
     * @param ResponseInterface $response PSR-7 response from surcharge advice operation
     */
    public function __construct(
        private readonly ResponseInterface $response,
    ) {
        // Parse response based on status code
        if ($this->isSuccessful()) {
            $this->surchargeAdvice = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->surchargeAdvice = null;
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
     * Gets the surcharge advice data (null if error response).
     *
     * @return SurchargeAdvice|null
     */
    public function getSurchargeAdvice(): ?SurchargeAdvice
    {
        return $this->surchargeAdvice;
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
     * Parses successful response body into SurchargeAdvice DTO.
     *
     * @return SurchargeAdvice
     * @throws InvalidArgumentException
     */
    private function parseSuccessResponse(): SurchargeAdvice
    {
        $data = $this->parseJsonBody();
        return SurchargeAdvice::fromData($data);
    }

}
