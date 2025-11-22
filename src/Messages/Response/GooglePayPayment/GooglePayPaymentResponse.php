<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\GooglePayPayment;

use Academe\Elavon\Epg\Psr7\Dtos\GooglePayPayment;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

class GooglePayPaymentResponse
{
    use HandlesErrors;

    private readonly ?GooglePayPayment $googlePayPayment;

    public function __construct(
        private readonly ResponseInterface $response,
    ) {
        if ($this->isSuccessful()) {
            $this->googlePayPayment = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->googlePayPayment = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    public static function fromPsr7Response(ResponseInterface $response): self
    {
        return new self($response);
    }

    public function getGooglePayPayment(): ?GooglePayPayment
    {
        return $this->googlePayPayment;
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function getPsr7Response(): ResponseInterface
    {
        return $this->response;
    }

    private function parseSuccessResponse(): GooglePayPayment
    {
        $data = $this->parseJsonBody();
        return GooglePayPayment::fromData($data);
    }
}
