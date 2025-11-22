<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\StoredAchPayment;

use Academe\Elavon\Epg\Psr7\Dtos\StoredAchPayment;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Create Stored ACH Payment Request.
 *
 * Builds a PSR-7 request for creating a stored ACH payment (POST /stored-ach-payments).
 *
 * Stored ACH payments allow merchants to charge customers for recurring payments without requiring
 * them to re-enter bank account details. The ACH payment can be initialized from a hosted ACH payment
 * or provided directly.
 *
 * Example usage with ElavonApiRequest decorator:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\StoredAchPayment\CreateStoredAchPaymentRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiRequest;
 * use Academe\Elavon\Epg\Psr7\Dtos\StoredAchPayment;
 *
 * // Build the stored ACH payment
 * $storedAchPayment = new StoredAchPayment(
 *     shopper: 'https://api.example.com/shoppers/s123',
 *     hostedAchPayment: 'https://api.example.com/hosted-ach-payments/hap456',
 *     customReference: 'customer-ach-789',
 * );
 *
 * // Build the request
 * $request = (new CreateStoredAchPaymentRequest($storedAchPayment))->build();
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
class CreateStoredAchPaymentRequest
{
    private readonly StoredAchPayment $storedAchPayment;

    /**
     * @param StoredAchPayment|array<string, mixed> $storedAchPayment Stored ACH payment data or array
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     * @param StreamFactoryInterface|null $streamFactory PSR-17 stream factory (uses built-in if null)
     *
     * @throws InvalidArgumentException When stored ACH payment data is invalid
     */
    public function __construct(
        StoredAchPayment|array $storedAchPayment,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
    ) {
        // Normalize to StoredAchPayment object
        $this->storedAchPayment = match (true) {
            $storedAchPayment instanceof StoredAchPayment => $storedAchPayment,
            is_array($storedAchPayment) => StoredAchPayment::fromData($storedAchPayment),
        };

        $this->validate();
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

        // Serialize stored ACH payment to JSON
        $data = $this->storedAchPayment->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request
        return $requestFactory
            ->createRequest('POST', '/stored-ach-payments')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withBody($streamFactory->createStream($json));
    }

    /**
     * Gets the stored ACH payment being created.
     *
     * @return StoredAchPayment
     */
    public function getStoredAchPayment(): StoredAchPayment
    {
        return $this->storedAchPayment;
    }

    /**
     * Validates the stored ACH payment data for creation.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // Shopper is required for creation
        if ($this->storedAchPayment->shopper === null) {
            throw new InvalidArgumentException('Shopper URL is required to create a stored ACH payment');
        }

        // Must have either hostedAchPayment or achPayment data
        if ($this->storedAchPayment->hostedAchPayment === null && $this->storedAchPayment->achPayment === null) {
            throw new InvalidArgumentException('Either hostedAchPayment URL or achPayment data is required to create a stored ACH payment');
        }
    }
}
