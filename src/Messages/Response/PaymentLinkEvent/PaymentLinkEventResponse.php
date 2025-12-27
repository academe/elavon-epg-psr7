<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\PaymentLinkEvent;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentLinkEvent;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

class PaymentLinkEventResponse
{
    use ParsesPsr7Response;

    public readonly ?PaymentLinkEvent $paymentLinkEvent;

    public function __construct(array $data, int $statusCode)
    {
        $this->statusCode = $statusCode;

        if ($this->isSuccessful()) {
            $this->paymentLinkEvent = PaymentLinkEvent::fromData($data);
            $this->error = null;
        } else {
            $this->paymentLinkEvent = null;
            $this->error = self::parseErrorData($data);
        }
    }
}
