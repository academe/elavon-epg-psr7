<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PaymentLink;

use Academe\Elavon\Epg\Psr7\Dtos\QueryParams;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;
use Psr\Http\Message\RequestInterface;

/**
 * Retrieve PaymentLink Event List Request.
 *
 * Builds a PSR-7 request for retrieving paginated payment link event lists
 * (GET /payment-links/{id}/payment-link-events).
 *
 * Events include actions like making a payment or sending an email reminder.
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Dtos\QueryParams;
 * use Academe\Elavon\Epg\Psr7\Messages\Request\PaymentLink\RetrievePaymentLinkEventListRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 *
 * // Build the base request with pagination
 * $queryParams = QueryParams::create()->withLimit(50);
 * $request = (new RetrievePaymentLinkEventListRequest('6xxFwvM8BqmM6T6DcF3DyTB3', $queryParams))->build();
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
class RetrievePaymentLinkEventListRequest
{
    use HasPsr17Factories;

    /**
     * @param string $paymentLinkId PaymentLink Resource ID
     * @param QueryParams $queryParams Query parameters for pagination/filtering
     * @throws InvalidArgumentException When payment link ID is empty
     */
    public function __construct(
        public readonly string $paymentLinkId,
        public readonly QueryParams $queryParams = new QueryParams()
    ) {
        if (empty($this->paymentLinkId)) {
            throw new InvalidArgumentException('PaymentLink ID cannot be empty');
        }
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{paymentLinkId: string, queryParams?: QueryParams|array<string, mixed>} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('paymentLinkId', $data)) {
            throw new InvalidArgumentException("Missing required key 'paymentLinkId' in data");
        }

        $queryParams = $data['queryParams'] ?? new QueryParams();

        if (is_array($queryParams)) {
            $queryParams = QueryParams::fromArray($queryParams);
        }

        return new static($data['paymentLinkId'], $queryParams);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        $request = $this->getRequestFactory()
            ->createRequest('GET', '/payment-links/' . $this->paymentLinkId . '/payment-link-events');

        if (! $this->queryParams->isEmpty()) {
            $request = $request->withUri($this->queryParams->apply($request->getUri()));
        }

        return $request;
    }
}
