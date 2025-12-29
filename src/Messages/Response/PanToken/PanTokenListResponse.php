<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\PanToken;

use Academe\Elavon\Epg\Psr7\Contracts\ResponseMessage;
use Academe\Elavon\Epg\Psr7\Dtos\PanToken;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

class PanTokenListResponse implements ResponseMessage
{
    use ParsesPsr7Response;

    /** @var array<PanToken>|null */
    public readonly ?array $panTokens;

    public function __construct(array $data, int $statusCode)
    {
        $this->statusCode = $statusCode;

        if ($this->isSuccessful()) {
            $this->panTokens = array_map(fn($item) => PanToken::fromData($item), $data);
            $this->error = null;
        } else {
            $this->panTokens = null;
            $this->error = self::parseErrorData($data);
        }
    }
}
