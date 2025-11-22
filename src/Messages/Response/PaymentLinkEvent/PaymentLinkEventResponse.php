<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\PaymentLinkEvent;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentLinkEvent;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

class PaymentLinkEventResponse
{
    use HandlesErrors;

    private readonly ?PaymentLinkEvent $paymentLinkEvent;

    public function __construct(private readonly ResponseInterface $response)
    {
        if ($this->isSuccessful()) {
            $this->paymentLinkEvent = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->paymentLinkEvent = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    public static function fromPsr7Response(ResponseInterface $response): self
    {
        return new self($response);
    }

    public function getPaymentLinkEvent(): ?PaymentLinkEvent
    {
        return $this->paymentLinkEvent;
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function getPsr7Response(): ResponseInterface
    {
        return $this->response;
    }

    private function parseSuccessResponse(): PaymentLinkEvent
    {
        $data = $this->parseJsonBody();
        return PaymentLinkEvent::fromData($data);
    }
}
