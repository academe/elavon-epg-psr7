<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\PaymentLink;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentLink;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

/**
 * PaymentLink Response.
 *
 * Parses a PSR-7 response from the EPG API containing either payment link data or error details.
 *
 * For successful responses (2xx), contains payment link data.
 * For error responses (4xx, 5xx), contains error details.
 *
 * Example usage:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Response\PaymentLink\PaymentLinkResponse;
 *
 * // Parse response from API
 * $response = PaymentLinkResponse::fromPsr7Response($psrResponse);
 *
 * if ($response->isSuccessful()) {
 *     $paymentLink = $response->getPaymentLink();
 *     echo "Payment Link ID: " . $paymentLink->id . "\n";
 *     echo "Payment URL: " . $paymentLink->url . "\n";
 * } else {
 *     $error = $response->getError();
 *     echo "Error: " . $error->message;
 * }
 * ```
 */
class PaymentLinkResponse
{
    use HandlesErrors;

    private readonly ?PaymentLink $paymentLink;

    /**
     * @param ResponseInterface $response PSR-7 response from the API
     *
     * @throws InvalidArgumentException When response cannot be parsed
     */
    public function __construct(
        private readonly ResponseInterface $response,
    ) {
        // Parse response based on status code
        if ($this->isSuccessful()) {
            $this->paymentLink = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->paymentLink = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    /**
     * Creates a PaymentLinkResponse from a PSR-7 response.
     *
     * @param ResponseInterface $response PSR-7 response
     *
     * @return self
     * @throws InvalidArgumentException When response cannot be parsed
     */
    public static function fromPsr7Response(ResponseInterface $response): self
    {
        return new self($response);
    }

    /**
     * Gets the parsed PaymentLink object.
     *
     * Only available for successful responses (2xx status codes).
     *
     * @return PaymentLink|null Returns null if response was an error
     */
    public function getPaymentLink(): ?PaymentLink
    {
        return $this->paymentLink;
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
     * Gets the original PSR-7 response.
     *
     * @return ResponseInterface
     */
    public function getPsr7Response(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Parses a successful response into a PaymentLink object.
     *
     * @return PaymentLink
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseSuccessResponse(): PaymentLink
    {
        $data = $this->parseJsonBody();
        return PaymentLink::fromData($data);
    }

}
