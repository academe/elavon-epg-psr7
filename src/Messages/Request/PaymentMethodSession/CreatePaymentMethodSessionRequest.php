<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PaymentMethodSession;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentMethodSession;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Create PaymentMethodSession Request.
 *
 * Builds a PSR-7 request for creating a payment method session (POST /payment-method-sessions).
 */
class CreatePaymentMethodSessionRequest
{
    use HasPsr17Factories;

    /**
     * @param PaymentMethodSession $paymentMethodSession PaymentMethodSession data
     */
    public function __construct(
        public readonly PaymentMethodSession $paymentMethodSession
    ) {
    }

    /**
     * @param array{paymentMethodSession: PaymentMethodSession|array<string, mixed>} $data
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('paymentMethodSession', $data)) {
            throw new InvalidArgumentException("Missing required key 'paymentMethodSession' in data");
        }

        $paymentMethodSession = $data['paymentMethodSession'] instanceof PaymentMethodSession
            ? $data['paymentMethodSession']
            : PaymentMethodSession::fromData($data['paymentMethodSession']);

        return new static($paymentMethodSession);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Serialize payment method session to JSON
        $data = $this->paymentMethodSession->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Build PSR-7 POST request
        return $this->getRequestFactory()
            ->createRequest('POST', '/payment-method-sessions')
            ->withBody($this->getStreamFactory()->createStream($json));
    }
}
