<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Order;

use Academe\Elavon\Epg\Psr7\Contracts\RequestMessage;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;
use Psr\Http\Message\RequestInterface;

/**
 * Retrieve Order Request.
 *
 * Builds a PSR-7 request for retrieving a single order (GET /orders/{id}).
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\RetrieveOrderRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 *
 * // Build the base request
 * $request = (new RetrieveOrderRequest('order123'))->build();
 *
 * // Or build from raw data
 * $request = RetrieveOrderRequest::fromData(['orderId' => 'order123'])->build();
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
class RetrieveOrderRequest implements RequestMessage
{
    use HasPsr17Factories;

    /**
     * @param string $orderId Order ID to retrieve
     *
     * @throws InvalidArgumentException When order ID is empty
     */
    public function __construct(
        public readonly string $orderId,
    ) {
        if (empty($this->orderId)) {
            throw new InvalidArgumentException('Order ID cannot be empty');
        }
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{orderId: string} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('orderId', $data)) {
            throw new InvalidArgumentException("Missing required key 'orderId' in data");
        }

        return new static($data['orderId']);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        return $this->getRequestFactory()
            ->createRequest('GET', '/orders/' . $this->orderId);
    }
}
