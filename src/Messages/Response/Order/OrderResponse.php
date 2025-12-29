<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\Order;

use Academe\Elavon\Epg\Psr7\Contracts\ResponseMessage;
use Academe\Elavon\Epg\Psr7\Dtos\Order;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

/**
 * Order Response.
 *
 * Parses API responses containing either order data or error details.
 *
 * Can be instantiated either from raw data or from a PSR-7 response:
 * - From data: new OrderResponse($data, $statusCode)
 * - From PSR-7: OrderResponse::fromPsr7Response($response)
 *
 * For successful responses (2xx), contains order data.
 * For error responses (4xx, 5xx), contains error details.
 */
class OrderResponse implements ResponseMessage
{
    use ParsesPsr7Response;

    public readonly ?Order $order;

    /**
     * @param array<string, mixed> $data Parsed response body data
     * @param int $statusCode HTTP status code
     */
    public function __construct(array $data, int $statusCode)
    {
        $this->statusCode = $statusCode;

        if ($this->isSuccessful()) {
            $this->order = Order::fromData($data);
            $this->error = null;
        } else {
            $this->order = null;
            $this->error = self::parseErrorData($data);
        }
    }
}
