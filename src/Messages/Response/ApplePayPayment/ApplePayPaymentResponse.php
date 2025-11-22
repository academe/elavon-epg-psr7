<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\ApplePayPayment;

use Academe\Elavon\Epg\Psr7\Dtos\ApplePayPayment;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

class ApplePayPaymentResponse
{
    use HandlesErrors;

    private readonly ?ApplePayPayment $applePayPayment;

    public function __construct(
        private readonly ResponseInterface $response,
    ) {
        if ($this->isSuccessful()) {
            $this->applePayPayment = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->applePayPayment = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    public static function fromPsr7Response(ResponseInterface $response): self
    {
        return new self($response);
    }

    public function getApplePayPayment(): ?ApplePayPayment
    {
        return $this->applePayPayment;
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function getPsr7Response(): ResponseInterface
    {
        return $this->response;
    }

    private function parseSuccessResponse(): ApplePayPayment
    {
        $data = $this->parseJsonBody();
        return ApplePayPayment::fromData($data);
    }
}
