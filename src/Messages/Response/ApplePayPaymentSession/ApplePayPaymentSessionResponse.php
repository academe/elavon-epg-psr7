<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\ApplePayPaymentSession;

use Academe\Elavon\Epg\Psr7\Dtos\ApplePayPaymentSession;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

class ApplePayPaymentSessionResponse
{
    use HandlesErrors;

    private readonly ?ApplePayPaymentSession $applePayPaymentSession;

    public function __construct(private readonly ResponseInterface $response)
    {
        if ($this->isSuccessful()) {
            $this->applePayPaymentSession = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->applePayPaymentSession = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    public static function fromPsr7Response(ResponseInterface $response): self
    {
        return new self($response);
    }

    public function getApplePayPaymentSession(): ?ApplePayPaymentSession
    {
        return $this->applePayPaymentSession;
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function getPsr7Response(): ResponseInterface
    {
        return $this->response;
    }

    private function parseSuccessResponse(): ApplePayPaymentSession
    {
        $data = $this->parseJsonBody();
        return ApplePayPaymentSession::fromData($data);
    }
}
