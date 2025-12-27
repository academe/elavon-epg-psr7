<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\Subscription;

use Academe\Elavon\Epg\Psr7\Dtos\Subscription;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

/**
 * Subscription Response.
 *
 * Parses a PSR-7 response from the EPG API containing either subscription data or error details.
 *
 * For successful responses (2xx), contains subscription data.
 * For error responses (4xx, 5xx), contains error details.
 */
class SubscriptionResponse
{
    use ParsesPsr7Response;

    public readonly ?Subscription $subscription;

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
            $this->subscription = Subscription::fromData($data);
            $this->error = null;
        } else {
            $this->subscription = null;
            $this->error = self::parseErrorData($data);
        }
    }
}
