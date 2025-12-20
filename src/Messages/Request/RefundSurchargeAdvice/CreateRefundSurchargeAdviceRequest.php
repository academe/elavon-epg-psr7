<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\RefundSurchargeAdvice;

use Academe\Elavon\Epg\Psr7\Dtos\RefundSurchargeAdvice;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Create Refund Surcharge Advice Request.
 *
 * Builds a PSR-7 request for creating a refund surcharge calculation advice (POST /refund-surcharge-advices).
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\CreateRefundSurchargeAdviceRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 * use Academe\Elavon\Epg\Psr7\Dtos\RefundSurchargeAdvice;
 * use Money\Money;
 *
 * // Build the refund surcharge advice request
 * $refundSurchargeAdvice = new RefundSurchargeAdvice(
 *     total: new Money('50.00', 'USD'),
 *     parentTransaction: 'https://api.example.com/transactions/txn123',
 * );
 *
 * $request = (new CreateRefundSurchargeAdviceRequest($refundSurchargeAdvice))->build();
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
class CreateRefundSurchargeAdviceRequest
{
    use HasPsr17Factories;

    private readonly RefundSurchargeAdvice $refundSurchargeAdvice;

    /**
     * @param RefundSurchargeAdvice|array<string, mixed> $refundSurchargeAdvice Refund surcharge advice data     *
     * @throws InvalidArgumentException When refund surcharge advice data is invalid
     */
    public function __construct(
        RefundSurchargeAdvice|array $refundSurchargeAdvice
    ) {
        // Normalize to RefundSurchargeAdvice object
        $this->refundSurchargeAdvice = match (true) {
            $refundSurchargeAdvice instanceof RefundSurchargeAdvice => $refundSurchargeAdvice,
            is_array($refundSurchargeAdvice) => RefundSurchargeAdvice::fromData($refundSurchargeAdvice),
        };

        $this->validate();
    }

    /**
     * Validates the refund surcharge advice request.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        if ($this->refundSurchargeAdvice->total === null) {
            throw new InvalidArgumentException('Total is required to create a refund surcharge advice');
        }

        if ($this->refundSurchargeAdvice->parentTransaction === null) {
            throw new InvalidArgumentException('Parent transaction is required to create a refund surcharge advice');
        }
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Use built-in factories if none provided
        $requestFactory = $this->getRequestFactory();
        $streamFactory = $this->getStreamFactory();

        // Serialize to JSON
        $data = $this->refundSurchargeAdvice->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request
        return $requestFactory
            ->createRequest('POST', '/refund-surcharge-advices')
            ->withBody($streamFactory->createStream($json));
    }

    /**
     * Gets the refund surcharge advice data.
     *
     * @return RefundSurchargeAdvice
     */
    public function getRefundSurchargeAdvice(): RefundSurchargeAdvice
    {
        return $this->refundSurchargeAdvice;
    }
}
