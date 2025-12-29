<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Contracts;

/**
 * Marker interface for request message builders.
 *
 * Classes implementing this interface are request builders/factories that
 * construct PSR-7 RequestInterface objects. They encapsulate the data and
 * logic needed to build an HTTP request for a specific API operation.
 *
 * Request message classes typically:
 * - Accept DTOs or primitive values in their constructor
 * - Provide a build() method that returns a PSR-7 RequestInterface
 * - Provide a fromData() factory method for hydration from arrays
 *
 * Example usage:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\Order\CreateOrderRequest;
 * use Academe\Elavon\Epg\Psr7\Dtos\Order;
 *
 * // Create the request builder with a DTO
 * $order = new Order(total: ['amount' => '100.00', 'currencyCode' => 'USD']);
 * $requestBuilder = new CreateOrderRequest($order);
 *
 * // Build the PSR-7 request
 * $psr7Request = $requestBuilder->build();
 *
 * // Send via any PSR-18 HTTP client
 * $response = $httpClient->sendRequest($psr7Request);
 * ```
 *
 * @see \Academe\Elavon\Epg\Psr7\Messages\Request
 */
interface RequestMessage extends Message
{
}
