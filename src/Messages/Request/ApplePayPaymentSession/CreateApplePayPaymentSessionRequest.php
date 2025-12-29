<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\ApplePayPaymentSession;

use Academe\Elavon\Epg\Psr7\Contracts\RequestMessage;
use Academe\Elavon\Epg\Psr7\Dtos\ApplePayPaymentSession;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

class CreateApplePayPaymentSessionRequest implements RequestMessage
{
    use HasPsr17Factories;

    public function __construct(
        public readonly ApplePayPaymentSession $applePayPaymentSession
    ) {
        if ($this->applePayPaymentSession->initiativeContext === null) {
            throw new InvalidArgumentException('Initiative context is required');
        }
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{applePayPaymentSession: ApplePayPaymentSession|array<string, mixed>} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('applePayPaymentSession', $data)) {
            throw new InvalidArgumentException("Missing required key 'applePayPaymentSession' in data");
        }

        $applePayPaymentSession = $data['applePayPaymentSession'] instanceof ApplePayPaymentSession
            ? $data['applePayPaymentSession']
            : ApplePayPaymentSession::fromData($data['applePayPaymentSession']);

        return new static($applePayPaymentSession);
    }

    public function build(): RequestInterface
    {
        $data = $this->applePayPaymentSession->toData();
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        return $this->getRequestFactory()
            ->createRequest('POST', '/apple-pay-payment-sessions')
            ->withBody($this->getStreamFactory()->createStream($json));
    }
}
