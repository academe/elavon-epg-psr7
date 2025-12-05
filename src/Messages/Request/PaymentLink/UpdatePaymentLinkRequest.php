<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PaymentLink;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentLink;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Update PaymentLink Request.
 *
 * Builds a PSR-7 request for updating a payment link (POST /payment-links/{id}).
 *
 * Payment links can be updated to change certain fields such as cancellation status,
 * return URL, or custom fields.
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\PaymentLink\UpdatePaymentLinkRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 * use Academe\Elavon\Epg\Psr7\Dtos\PaymentLink;
 *
 * // Build the payment link update
 * $paymentLink = new PaymentLink(
 *     doCancel: true,
 *     customReference: 'Updated reference',
 * );
 *
 * // Build the request
 * $request = (new UpdatePaymentLinkRequest('6xxFwvM8BqmM6T6DcF3DyTB3', $paymentLink))->build();
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
class UpdatePaymentLinkRequest
{
    private readonly PaymentLink $paymentLink;

    /**
     * @param string $paymentLinkId PaymentLink Resource ID to update
     * @param PaymentLink|array<string, mixed> $paymentLink PaymentLink data or array (only fields to update)
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     * @param StreamFactoryInterface|null $streamFactory PSR-17 stream factory (uses built-in if null)
     *
     * @throws InvalidArgumentException When payment link ID is empty or data is invalid
     */
    public function __construct(
        private readonly string $paymentLinkId,
        PaymentLink|array $paymentLink,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
    ) {
        if (empty($this->paymentLinkId)) {
            throw new InvalidArgumentException('PaymentLink ID cannot be empty');
        }

        // Normalize to PaymentLink object
        $this->paymentLink = match (true) {
            $paymentLink instanceof PaymentLink => $paymentLink,
            is_array($paymentLink) => PaymentLink::fromData($paymentLink),
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

        // Serialize payment link to JSON
        $data = $this->paymentLink->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request
        return $requestFactory
            ->createRequest('POST', '/payment-links/' . $this->paymentLinkId)
            ->withBody($streamFactory->createStream($json));
    }

    /**
     * Gets the payment link ID being updated.
     *
     * @return string
     */
    public function getPaymentLinkId(): string
    {
        return $this->paymentLinkId;
    }

    /**
     * Gets the payment link data for the update.
     *
     * @return PaymentLink
     */
    public function getPaymentLink(): PaymentLink
    {
        return $this->paymentLink;
    }
}
