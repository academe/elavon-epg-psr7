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
 * Create Order Request.
 *
 * Builds a PSR-7 request for creating an order (POST /orders).
 *
 * Orders detail what a shopper is paying for, including line items,
 * shipping information, and references.
 *
 * Example usage with ElavonApiRequest decorator:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\CreateOrderRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiRequest;
 * use Academe\Elavon\Epg\Psr7\Dtos\Order;
 *
 * // Build the order
 * $order = new Order(
 *     total: ['amount' => '100.00', 'currencyCode' => 'USD'],
 *     description: 'March 2025 Rent',
 *     shopperEmailAddress: 'shopper@example.com',
 * );
 *
 * // Build the request
 * $request = (new CreateOrderRequest($order))->build();
 *
 * // Add Elavon API headers, environment, and authentication
 * $elavonRequest = ElavonApiRequest::create($request)
 *     ->withSandbox()
 *     ->withAuthentication($merchantAlias, $apiKey);
 *
 * // Send the request
 * $response = $httpClient->sendRequest($elavonRequest);
 * ```
 *
 * Note: This class builds the base request but does NOT add:
 * - Elavon API headers (Accept, Accept-Version)
 * - Environment configuration (sandbox, production, custom base URI)
 * - Authentication headers (Authorization)
 * Use the ElavonApiRequest decorator to add these via fluent interface.
 */
class CreateOrderRequest
{
    private readonly Order $order;

    /**
     * @param Order|array<string, mixed> $order Order data or array
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     * @param StreamFactoryInterface|null $streamFactory PSR-17 stream factory (uses built-in if null)
     *
     * @throws InvalidArgumentException When order data is invalid
     */
    public function __construct(
        Order|array $order,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
    ) {
        // Normalize to Order object
        $this->order = match (true) {
            $order instanceof Order => $order,
            is_array($order) => Order::fromData($order),
        };

        // Validate required fields for creation
        $this->validateOrderRequest($this->order);
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
            ->createRequest('POST', '/orders')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withBody($streamFactory->createStream($json));
    }

    /**
     * Gets the order being created.
     *
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->order;
    }

    /**
     * Validates that required fields are present for an order creation request.
     *
     * @param Order $order
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
