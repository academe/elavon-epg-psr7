<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\Order;

use Academe\Elavon\Epg\Psr7\Contracts\ResponseMessage;
use Academe\Elavon\Epg\Psr7\Dtos\Order;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

/**
 * Order List Response.
 *
 * Parses PSR-7 responses for paginated order lists (GET /orders).
 *
 * Example usage:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Response\OrderListResponse;
 *
 * // Parse response from API
 * $response = OrderListResponse::fromPsr7Response($psrResponse);
 *
 * if ($response->isSuccessful()) {
 *     foreach ($response->getOrders() as $order) {
 *         echo "Order ID: " . $order->id . "\n";
 *     }
 *
 *     if ($response->hasMorePages()) {
 *         $nextPageUrl = $response->getNext();
 *         // Fetch next page...
 *     }
 * } else {
 *     $error = $response->getError();
 *     echo "Error: " . $error->message;
 * }
 * ```
 */
class OrderListResponse implements ResponseMessage
{
    use ParsesPsr7Response;

    /** @var array<Order>|null */
    public readonly ?array $orders;
    public readonly ?string $nextPage;
    public readonly ?string $nextPageToken;
    public readonly ?string $firstPage;

    /**
     * @param array<string, mixed> $data Parsed response body data
     * @param int $statusCode HTTP status code
     * @throws InvalidArgumentException When response format is invalid
     */
    public function __construct(array $data, int $statusCode)
    {
        $this->statusCode = $statusCode;

        // Parse response based on status code
        if ($this->isSuccessful()) {
            $parsed = $this->parseSuccessData($data);
            $this->orders = $parsed['items'];
            $this->nextPage = $parsed['next'];
            $this->nextPageToken = $parsed['nextPageToken'];
            $this->firstPage = $parsed['first'];
            $this->error = null;
        } else {
            $this->orders = null;
            $this->nextPage = null;
            $this->nextPageToken = null;
            $this->firstPage = null;
            $this->error = self::parseErrorData($data);
        }
    }

    /**
     * Checks if there are more pages of results.
     *
     * @return bool True if more pages exist, false otherwise
     */
    public function hasMorePages(): bool
    {
        return $this->nextPage !== null;
    }
    /**
     * Parses a successful response into a paginated list of orders.
     *
     * @return array{items: array<Order>, next: string|null, nextPageToken: string|null, first: string|null}
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseSuccessData(array $data): array
    {
        // Validate structure
        if (!isset($data['items']) || !is_array($data['items'])) {
            throw new InvalidArgumentException('Response must contain an "items" array');
        }

        // Parse each order
        $orders = [];
        foreach ($data['items'] as $index => $itemData) {
            if (!is_array($itemData)) {
                throw new InvalidArgumentException("Item at index {$index} is not an array");
            }

            $orders[] = Order::fromData($itemData);
        }
        return [
            'items' => $orders,
            'next' => isset($data['next']) ? (string) $data['next'] : null,
            'nextPageToken' => isset($data['nextPageToken']) ? (string) $data['nextPageToken'] : null,
            'first' => isset($data['first']) ? (string) $data['first'] : null,
        ];
    }

}
