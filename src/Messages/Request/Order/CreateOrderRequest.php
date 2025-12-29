<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Order;

use Academe\Elavon\Epg\Psr7\Contracts\RequestMessage;
use Academe\Elavon\Epg\Psr7\Dtos\Order;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Create Order Request.
 *
 * Builds a PSR-7 request for creating an order (POST /orders).
 *
 * Orders detail what a shopper is paying for, including line items,
 * shipping information, and references.
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\CreateOrderRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 * use Academe\Elavon\Epg\Psr7\Dtos\Order;
 *
 * // Build the order using a hydrated Order object
 * $order = new Order(
 *     total: ['amount' => '100.00', 'currencyCode' => 'USD'],
 *     description: 'March 2025 Rent',
 *     shopperEmailAddress: 'shopper@example.com',
 * );
 * $request = (new CreateOrderRequest($order))->build();
 *
 * // Or build from raw data
 * $request = CreateOrderRequest::fromData([
 *     'order' => ['total' => ['amount' => '100.00', 'currencyCode' => 'USD']],
 * ])->build();
 *
 * // Add Elavon API headers, environment, and authentication
 * $factory = ElavonApiFactory::configure()
 *     ->withRegion('eu')
 *     ->withEnvironment('sandbox')
 *     ->withAuthentication($merchantAlias, $apiKey);
 *
 * // Send the request
 * $apiRequest = $factory->apply($request);
 * $response = $httpClient->sendRequest($apiRequest);
 * ```
 *
 * Note: This class builds the base request but does NOT add:
 * - Elavon API headers (Accept, Accept-Version)
 * - Environment configuration (sandbox, production, custom base URI)
 * - Authentication headers (Authorization)
 * Use the ElavonApiFactory to add these.
 */
class CreateOrderRequest implements RequestMessage
{
    use HasPsr17Factories;

    /**
     * @param Order $order Order data
     *
     * @throws InvalidArgumentException When order data is invalid
     */
    public function __construct(
        public readonly Order $order,
    ) {
        $this->validateOrderRequest($this->order);
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{order: Order|array<string, mixed>} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('order', $data)) {
            throw new InvalidArgumentException("Missing required key 'order' in data");
        }

        $order = $data['order'] instanceof Order
            ? $data['order']
            : Order::fromData($data['order']);

        return new static($order);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        $body = json_encode($this->order->toData(), JSON_THROW_ON_ERROR);

        return $this->getRequestFactory()
            ->createRequest('POST', '/orders')
            ->withBody($this->getStreamFactory()->createStream($body));
    }

    /**
     * Validates that required fields are present for an order creation request.
     *
     * @throws InvalidArgumentException When required fields are missing
     */
    private function validateOrderRequest(Order $order): void
    {
        // According to OpenAPI spec, 'total' is required for OrderInput
        if ($order->total === null) {
            throw new InvalidArgumentException('Order total is required for creating an order');
        }
    }
}
