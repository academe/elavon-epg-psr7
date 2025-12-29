<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\ApplePayPaymentSession;

use Academe\Elavon\Epg\Psr7\Contracts\ResponseMessage;
use Academe\Elavon\Epg\Psr7\Dtos\ApplePayPaymentSession;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

class ApplePayPaymentSessionResponse implements ResponseMessage
{
    use ParsesPsr7Response;

    public readonly ?ApplePayPaymentSession $applePayPaymentSession;

    public function __construct(array $data, int $statusCode)
    {
        $this->statusCode = $statusCode;

        if ($this->isSuccessful()) {
            $this->applePayPaymentSession = ApplePayPaymentSession::fromData($data);
            $this->error = null;
        } else {
            $this->applePayPaymentSession = null;
            $this->error = self::parseErrorData($data);
        }
    }
}
