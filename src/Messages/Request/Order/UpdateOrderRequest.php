<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Order;

use Academe\Elavon\Epg\Psr7\Dtos\Order;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

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
class UpdateOrderRequest
{
    private readonly Order $order;

    /**
     * @param string $orderId Order ID to update
     * @param Order|array<string, mixed> $order Updated order data
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     * @param StreamFactoryInterface|null $streamFactory PSR-17 stream factory (uses built-in if null)
     *
     * @throws InvalidArgumentException When order ID is empty or order data is invalid
     */
    public function __construct(
        private readonly string $orderId,
        Order|array $order,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
    ) {
        if (empty($this->orderId)) {
            throw new InvalidArgumentException('Order ID cannot be empty');
        }

        // Normalize to Order object
        $this->order = match (true) {
            $order instanceof Order => $order,
            is_array($order) => Order::fromData($order),
        };
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Use built-in factories if none provided
        $requestFactory = $this->requestFactory ?? new Psr17Factory();
        $streamFactory = $this->streamFactory ?? new Psr17Factory();

        // Serialize order to JSON
        $data = $this->order->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request
        return $requestFactory
            ->createRequest('POST', '/orders/' . $this->orderId)
            ->withBody($streamFactory->createStream($json));
    }

    /**
     * Gets the order ID being updated.
     *
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * Gets the order data.
     *
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->order;
    }
}
