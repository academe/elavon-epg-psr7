<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request;

use Academe\Elavon\Epg\Psr7\Dtos\RefundSurchargeAdvice;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Create Refund Surcharge Advice Request.
 *
 * Builds a PSR-7 request for creating a refund surcharge calculation advice (POST /refund-surcharge-advices).
 *
 * Example usage with ElavonApiRequest decorator:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\CreateRefundSurchargeAdviceRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiRequest;
 * use Academe\Elavon\Epg\Psr7\Dtos\RefundSurchargeAdvice;
 * use Academe\Elavon\Epg\Psr7\ValueObjects\Money;
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
class CreateRefundSurchargeAdviceRequest
{
    private readonly RefundSurchargeAdvice $refundSurchargeAdvice;

    /**
     * @param RefundSurchargeAdvice|array<string, mixed> $refundSurchargeAdvice Refund surcharge advice data
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     * @param StreamFactoryInterface|null $streamFactory PSR-7 stream factory (uses built-in if null)
     * @param string $baseUri Base URI for the API (e.g., "https://api.eu.elavonpayments.com")
     *
     * @throws InvalidArgumentException When refund surcharge advice data is invalid
     */
    public function __construct(
        RefundSurchargeAdvice|array $refundSurchargeAdvice,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
        private readonly string $baseUri = 'https://api.eu.elavonpayments.com',
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
        $requestFactory = $this->requestFactory ?? new Psr17Factory();
        $streamFactory = $this->streamFactory ?? new Psr17Factory();

        // Serialize to JSON
        $data = $this->refundSurchargeAdvice->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request
        return $requestFactory
            ->createRequest('POST', $this->baseUri . '/refund-surcharge-advices')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
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
