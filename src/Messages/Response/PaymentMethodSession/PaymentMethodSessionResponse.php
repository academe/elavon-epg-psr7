<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\PaymentMethodSession;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentMethodSession;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

/**
 * PaymentMethodSession Response.
 *
 * Parses a PSR-7 response from the EPG API containing either payment method session data or error details.
 */
class PaymentMethodSessionResponse
{
    use ParsesPsr7Response;

    public readonly ?PaymentMethodSession $paymentMethodSession;

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
            $this->paymentMethodSession = PaymentMethodSession::fromData($data);
            $this->error = null;
        } else {
            $this->paymentMethodSession = null;
            $this->error = self::parseErrorData($data);
        }
    }
}
