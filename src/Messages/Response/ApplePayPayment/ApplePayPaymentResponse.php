<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\ApplePayPayment;

use Academe\Elavon\Epg\Psr7\Contracts\ResponseMessage;
use Academe\Elavon\Epg\Psr7\Dtos\ApplePayPayment as ApplePayPaymentDto;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

class ApplePayPaymentResponse implements ResponseMessage
{
    use ParsesPsr7Response;

    public readonly ?ApplePayPaymentDto $applePayPayment;

    public function __construct(array $data, int $statusCode) {
        $this->statusCode = $statusCode;

        if ($this->isSuccessful()) {
            $this->applePayPayment = ApplePayPaymentDto::fromData($data);
            $this->error = null;
        } else {
            $this->applePayPayment = null;
            $this->error = self::parseErrorData($data);
        }
    }
}
