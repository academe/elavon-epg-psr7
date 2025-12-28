<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\HostedAchPayment;

use Academe\Elavon\Epg\Psr7\Dtos\HostedAchPayment;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Create Hosted ACH Payment Request.
 *
 * Builds a PSR-7 request for creating a hosted ACH payment (POST /hosted-ach-payments).
 *
 * Hosted ACH payments allow secure bank account data collection without the merchant handling sensitive data.
 * The ACH payment data is collected and stored temporarily for single-use in a transaction.
 *
 * Example usage with ElavonApiFactory:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\HostedAchPayment\CreateHostedAchPaymentRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
 * use Academe\Elavon\Epg\Psr7\Dtos\HostedAchPayment;
 * use Academe\Elavon\Epg\Psr7\Dtos\AchPayment;
 * use Academe\Elavon\Epg\Psr7\Enums\AchAccountType;
 *
 * // Build the hosted ACH payment
 * $achPayment = new AchPayment(
 *     achAccountType: AchAccountType::CHECKING_PERSONAL,
 *     accountName: 'John Doe',
 *     bankRoutingNumber: '123456789',
 *     bankAccountNumber: '9876543210',
 * );
 * $hostedAchPayment = new HostedAchPayment(achPayment: $achPayment);
 *
 * // Build the request
 * $request = (new CreateHostedAchPaymentRequest($hostedAchPayment))->build();
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
class CreateHostedAchPaymentRequest
{
    use HasPsr17Factories;

    /**
     * @param HostedAchPayment $hostedAchPayment Hosted ACH payment data     *
     * @throws InvalidArgumentException When hosted ACH payment data is invalid
     */
    public function __construct(
        public readonly HostedAchPayment $hostedAchPayment
    ) {
        $this->validate();
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{hostedAchPayment: HostedAchPayment|array<string, mixed>} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('hostedAchPayment', $data)) {
            throw new InvalidArgumentException("Missing required key 'hostedAchPayment' in data");
        }

        $hostedAchPayment = $data['hostedAchPayment'] instanceof HostedAchPayment
            ? $data['hostedAchPayment']
            : HostedAchPayment::fromData($data['hostedAchPayment']);

        return new static($hostedAchPayment);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Serialize hosted ACH payment to JSON
        $data = $this->hostedAchPayment->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request
        return $this->getRequestFactory()
            ->createRequest('POST', '/hosted-ach-payments')
            ->withBody($this->getStreamFactory()->createStream($json));
    }

    /**
     * Validates the hosted ACH payment data for creation.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // ACH payment is required for creation
        if ($this->hostedAchPayment->achPayment === null) {
            throw new InvalidArgumentException('ACH payment data is required to create a hosted ACH payment');
        }
    }
}
