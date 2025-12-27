<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\ForexAdvice;

use Academe\Elavon\Epg\Psr7\Dtos\ErrorResponse;
use Academe\Elavon\Epg\Psr7\Dtos\ForexAdvice;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;
use Psr\Http\Message\ResponseInterface;

/**
 * Forex Advice Response.
 *
 * Parses responses from forex advice operations (create, retrieve).
 *
 * Example usage:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Response\ForexAdviceResponse;
 *
 * // Parse the PSR-7 response
 * $response = new ForexAdviceResponse($psrResponse);
 *
 * if ($response->isSuccessful()) {
 *     $forexAdvice = $response->getForexAdvice();
 *     echo "Conversion rate: " . $forexAdvice->conversionRate;
 *     echo "Issuer total: " . $forexAdvice->issuerTotal->amount;
 * } else {
 *     $error = $response->getError();
 *     echo "Error: " . $error->status;
 * }
 * ```
 */
class ForexAdviceResponse
{
    use ParsesPsr7Response;

    public readonly ?ForexAdvice $forexAdvice;

    /**
     * @param array<string, mixed> $data Parsed response body data
     * @param int $statusCode HTTP status code
     */
    public function __construct(array $data, int $statusCode) {
        $this->statusCode = $statusCode;

        // Parse response based on status code
        if ($this->isSuccessful()) {
            $this->forexAdvice = ForexAdvice::fromData($data);
            $this->error = null;
        } else {
            $this->forexAdvice = null;
            $this->error = self::parseErrorData($data);
        }
    }
    /**
     * Gets the underlying PSR-7 response.
     *
     * @return ResponseInterface
     */
    /**
     * Parses successful response body into ForexAdvice DTO.
     *
     * @return ForexAdvice
     * @throws InvalidArgumentException
     */
}
