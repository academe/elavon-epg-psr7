<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Contracts;

/**
 * Marker interface for response message parsers.
 *
 * Classes implementing this interface are response parsers/wrappers that
 * consume decoded PSR-7 response body data and expose typed DTOs. They
 * handle success/error branching and provide structured access to the
 * API response data.
 *
 * Response message classes typically:
 * - Accept raw array data and HTTP status code in their constructor
 * - Parse the data into typed DTOs on success
 * - Capture error details on failure
 * - Provide a fromResponse() factory method for PSR-7 ResponseInterface
 *
 * Example usage:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Response\Order\OrderResponse;
 *
 * // Parse from a PSR-7 response
 * $orderResponse = OrderResponse::fromResponse($psr7Response);
 *
 * if ($orderResponse->isSuccessful()) {
 *     $order = $orderResponse->order; // Typed Order DTO
 *     echo $order->id;
 * } else {
 *     $error = $orderResponse->error; // Error details
 *     echo $error->message;
 * }
 * ```
 *
 * @see \Academe\Elavon\Epg\Psr7\Messages\Response
 */
interface ResponseMessage extends Message
{
    public function isSuccessful(): bool;
}
