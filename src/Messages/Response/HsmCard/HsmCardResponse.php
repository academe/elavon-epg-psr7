<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\HsmCard;

use Academe\Elavon\Epg\Psr7\Dtos\HsmCard;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

class HsmCardResponse
{
    use ParsesPsr7Response;

    public readonly ?HsmCard $hsmCard;

    public function __construct(array $data, int $statusCode)
    {
        $this->statusCode = $statusCode;

        if ($this->isSuccessful()) {
            $this->hsmCard = HsmCard::fromData($data);
            $this->error = null;
        } else {
            $this->hsmCard = null;
            $this->error = self::parseErrorData($data);
        }
    }
}
