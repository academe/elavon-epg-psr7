<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\Shopper;

use Academe\Elavon\Epg\Psr7\Contracts\ResponseMessage;
use Academe\Elavon\Epg\Psr7\Dtos\Shopper;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

/**
 * Shopper Response.
 *
 * Parses PSR-7 responses for shopper operations (create, retrieve, update).
 */
class ShopperResponse implements ResponseMessage
{
    use ParsesPsr7Response;

    public readonly ?Shopper $shopper;

    /**
     * @param array<string, mixed> $data Parsed response body data
     * @param int $statusCode HTTP status code
     */
    public function __construct(array $data, int $statusCode)
    {
        $this->statusCode = $statusCode;

        if ($this->isSuccessful()) {
            $this->shopper = Shopper::fromData($data);
            $this->error = null;
        } else {
            $this->shopper = null;
            $this->error = self::parseErrorData($data);
        }
    }
}
