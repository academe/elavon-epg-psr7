<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Order;

use Academe\Elavon\Epg\Psr7\Contracts\RequestMessage;
use Academe\Elavon\Epg\Psr7\Dtos\Order;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Update Order Request.
 *
 * Builds a PSR-7 request for updating an order (POST /orders/{id}).
 *
 * This overwrites an existing order with new data.
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\UpdateOrderRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 * use Academe\Elavon\Epg\Psr7\Dtos\Order;
 *
 * // Build the updated order
 * $order = new Order(
 *     total: ['amount' => '150.00', 'currencyCode' => 'USD'],
 *     description: 'Updated order description',
 * );
 *
 * // Build the request
 * $request = (new UpdateOrderRequest('order123', $order))->build();
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
class UpdateOrderRequest implements RequestMessage
{
    use HasPsr17Factories;

    /**
     * @param string $orderId Order ID to update
     * @param Order $order Updated order data     *
     * @throws InvalidArgumentException When order ID is empty
     */
    public function __construct(
        public readonly string $orderId,
        public readonly Order $order
    ) {
        if (empty($this->orderId)) {
            throw new InvalidArgumentException('Order ID cannot be empty');
        }
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{orderId: string, order: Order|array<string, mixed>} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('orderId', $data)) {
            throw new InvalidArgumentException("Missing required key 'orderId' in data");
        }

        if (! array_key_exists('order', $data)) {
            throw new InvalidArgumentException("Missing required key 'order' in data");
        }

        $order = $data['order'] instanceof Order
            ? $data['order']
            : Order::fromData($data['order']);

        return new static($data['orderId'], $order);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Serialize order to JSON
        $data = $this->order->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request
        return $this->getRequestFactory()
            ->createRequest('POST', '/orders/' . $this->orderId)
            ->withBody($this->getStreamFactory()->createStream($json));
    }
}
