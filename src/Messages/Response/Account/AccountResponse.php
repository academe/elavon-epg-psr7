<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\Account;

use Academe\Elavon\Epg\Psr7\Contracts\ResponseMessage;
use Academe\Elavon\Epg\Psr7\Dtos\Account;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

/**
 * Account Response.
 *
 * Parses a PSR-7 response from the EPG API containing either account data or error details.
 *
 * For successful responses (2xx), contains account data.
 * For error responses (4xx, 5xx), contains error details.
 */
class AccountResponse implements ResponseMessage
{
    use ParsesPsr7Response;

    public readonly ?Account $account;

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
            $this->account = Account::fromData($data);
            $this->error = null;
        } else {
            $this->account = null;
            $this->error = self::parseErrorData($data);
        }
    }
}
