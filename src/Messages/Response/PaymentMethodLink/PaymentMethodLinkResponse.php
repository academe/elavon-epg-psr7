<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\PaymentMethodLink;

use Academe\Elavon\Epg\Psr7\Contracts\ResponseMessage;
use Academe\Elavon\Epg\Psr7\Dtos\PaymentMethodLink;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

/**
 * PaymentMethodLink Response.
 *
 * Parses a PSR-7 response from the EPG API containing either payment method link data or error details.
 *
 * For successful responses (2xx), contains payment method link data.
 * For error responses (4xx, 5xx), contains error details.
 */
class PaymentMethodLinkResponse implements ResponseMessage
{
    use ParsesPsr7Response;

    public readonly ?PaymentMethodLink $paymentMethodLink;

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
            $this->paymentMethodLink = PaymentMethodLink::fromData($data);
            $this->error = null;
        } else {
            $this->paymentMethodLink = null;
            $this->error = self::parseErrorData($data);
        }
    }
}
