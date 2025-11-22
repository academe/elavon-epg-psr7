<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\HsmCard;

use Academe\Elavon\Epg\Psr7\Dtos\HsmCard;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

class HsmCardResponse
{
    use HandlesErrors;

    private readonly ?HsmCard $hsmCard;

    public function __construct(private readonly ResponseInterface $response)
    {
        if ($this->isSuccessful()) {
            $this->hsmCard = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->hsmCard = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    public static function fromPsr7Response(ResponseInterface $response): self
    {
        return new self($response);
    }

    public function getHsmCard(): ?HsmCard
    {
        return $this->hsmCard;
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function getPsr7Response(): ResponseInterface
    {
        return $this->response;
    }

    private function parseSuccessResponse(): HsmCard
    {
        $data = $this->parseJsonBody();
        return HsmCard::fromData($data);
    }
}
