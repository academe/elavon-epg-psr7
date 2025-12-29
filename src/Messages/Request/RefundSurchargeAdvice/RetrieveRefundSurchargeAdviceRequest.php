<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\RefundSurchargeAdvice;

use Academe\Elavon\Epg\Psr7\Contracts\RequestMessage;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Retrieve Refund Surcharge Advice Request.
 *
 * Builds a PSR-7 request for retrieving a single refund surcharge advice (GET /refund-surcharge-advices/{id}).
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\RetrieveRefundSurchargeAdviceRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 *
 * // Build the base request
 * $request = (new RetrieveRefundSurchargeAdviceRequest('rsa123'))->build();
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
class RetrieveRefundSurchargeAdviceRequest implements RequestMessage
{
    use HasPsr17Factories;

    /**
     * @param string $refundSurchargeAdviceId Refund surcharge advice ID to retrieve     *
     * @throws InvalidArgumentException When refund surcharge advice ID is empty
     */
    public function __construct(
        public readonly string $refundSurchargeAdviceId
    ) {
        if (empty($this->refundSurchargeAdviceId)) {
            throw new InvalidArgumentException('Refund surcharge advice ID cannot be empty');
        }
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{refundSurchargeAdviceId: string} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('refundSurchargeAdviceId', $data)) {
            throw new InvalidArgumentException("Missing required key 'refundSurchargeAdviceId' in data");
        }

        return new static($data['refundSurchargeAdviceId']);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Build PSR-7 GET request
        return $this->getRequestFactory()
            ->createRequest('GET', '/refund-surcharge-advices/' . $this->refundSurchargeAdviceId);
    }
}
