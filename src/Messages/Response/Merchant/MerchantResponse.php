<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\Merchant;

use Academe\Elavon\Epg\Psr7\Contracts\ResponseMessage;
use Academe\Elavon\Epg\Psr7\Dtos\Merchant;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

/**
 * Merchant Response.
 *
 * Parses a PSR-7 response from the EPG API containing either merchant data or error details.
 *
 * For successful responses (2xx), contains merchant data.
 * For error responses (4xx, 5xx), contains error details.
 */
class MerchantResponse implements ResponseMessage
{
    use ParsesPsr7Response;

    public readonly ?Merchant $merchant;

    /**
     * @param array<string, mixed> $data Parsed response body data
     * @param int $statusCode HTTP status code
     *
     * @throws InvalidArgumentException When response cannot be parsed
     */
    public function __construct(array $data, int $statusCode) {
        $this->statusCode = $statusCode;

        // Parse response based on status code
        if ($this->isSuccessful()) {
            $this->merchant = Merchant::fromData($data);
            $this->error = null;
        } else {
            $this->merchant = null;
            $this->error = self::parseErrorData($data);
        }
    }
}
