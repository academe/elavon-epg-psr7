<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\PaymentSession;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentSession;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

/**
 * PaymentSession Response.
 *
 * Parses a PSR-7 response from the EPG API containing either payment session data or error details.
 *
 * For successful responses (2xx), contains payment session data.
 * For error responses (4xx, 5xx), contains error details.
 */
class PaymentSessionResponse
{
    use ParsesPsr7Response;

    public readonly ?PaymentSession $paymentSession;

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
            $this->paymentSession = PaymentSession::fromData($data);
            $this->error = null;
        } else {
            $this->paymentSession = null;
            $this->error = self::parseErrorData($data);
        }
    }
}
