<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\PaymentLink;

use Academe\Elavon\Epg\Psr7\Contracts\ResponseMessage;
use Academe\Elavon\Epg\Psr7\Dtos\PaymentLink;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

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
class PaymentLinkResponse implements ResponseMessage
{
    use ParsesPsr7Response;

    public readonly ?PaymentLink $paymentLink;

    /**
     * @param array<string, mixed> $data Parsed response body data
     * @param int $statusCode HTTP status code
     *
     * @throws InvalidArgumentException When response cannot be parsed
     */
    public function __construct(array $data, int $statusCode) {
        $this->statusCode = $statusCode;

        // Parse response based on status code
        if ($this->isSuccessful()) {
            $this->paymentLink = PaymentLink::fromData($data);
            $this->error = null;
        } else {
            $this->paymentLink = null;
            $this->error = self::parseErrorData($data);
        }
    }
}
