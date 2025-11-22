<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\PazePayment;

use Academe\Elavon\Epg\Psr7\Dtos\PazePayment;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

class PazePaymentResponse
{
    use HandlesErrors;

    private readonly ?PazePayment $pazePayment;

    public function __construct(
        private readonly ResponseInterface $response,
    ) {
        if ($this->isSuccessful()) {
            $this->pazePayment = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->pazePayment = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    public static function fromPsr7Response(ResponseInterface $response): self
    {
        return new self($response);
    }

    public function getPazePayment(): ?PazePayment
    {
        return $this->pazePayment;
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function getPsr7Response(): ResponseInterface
    {
        return $this->response;
    }

    private function parseSuccessResponse(): PazePayment
    {
        $data = $this->parseJsonBody();
        return PazePayment::fromData($data);
    }
}
