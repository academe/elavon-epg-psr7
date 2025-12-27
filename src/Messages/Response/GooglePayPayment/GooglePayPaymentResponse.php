<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\GooglePayPayment;

use Academe\Elavon\Epg\Psr7\Dtos\GooglePayPayment;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

class GooglePayPaymentResponse
{
    use ParsesPsr7Response;

    public readonly ?GooglePayPayment $googlePayPayment;

    public function __construct(array $data, int $statusCode) {
        $this->statusCode = $statusCode;

        if ($this->isSuccessful()) {
            $this->googlePayPayment = GooglePayPayment::fromData($data);
            $this->error = null;
        } else {
            $this->googlePayPayment = null;
            $this->error = self::parseErrorData($data);
        }
    }
}
