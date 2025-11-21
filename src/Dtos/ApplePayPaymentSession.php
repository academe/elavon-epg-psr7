<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * ApplePayPaymentSession data transfer object.
 *
 * Contains the elements necessary for initiating a transaction with Apple.
 * The Apple Pay payment session is returned after validation of the supplied fields.
 *
 * Properties marked [Response] are typically only present in API responses.
 * Properties marked [Request] are typically sent in API requests.
 */
class ApplePayPaymentSession implements DataTransferObject
{
    use SerializesData;

    /**
     * Get property type definitions for this DTO.
     *
     * @return array<string, array<string>>
     */
    public static function getPropertyTypes(): array
    {
        return [
            'string' => [
                'merchant', 'account', 'displayName',
                'initiativeContext', 'paymentSession',
            ],
        ];
    }

    /**
     * @param string|null $merchant [Response] Merchant Resource URL (suppressed when a public API key is used)
     * @param string|null $account Account Resource URL
     * @param string|null $displayName [Response] DBA name of the store
     * @param string|null $initiativeContext Fully qualified domain name that has been registered with Apple (required for creation, does not include protocol)
     * @param string|null $paymentSession [Response] Apple Pay payment session object
     *
     * @throws InvalidArgumentException When validation fails
     */
    public function __construct(
        // Response-only fields
        public readonly ?string $merchant = null,
        public readonly ?string $displayName = null,
        public readonly ?string $paymentSession = null,

        // Request/Response fields
        public readonly ?string $account = null,
        public readonly ?string $initiativeContext = null,
    ) {
        $this->validate();
    }

    /**
     * Validates Apple Pay payment session data.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // initiativeContext should be a domain name without protocol
        if ($this->initiativeContext !== null) {
            if (preg_match('/^https?:\/\//', $this->initiativeContext)) {
                throw new InvalidArgumentException(
                    'Initiative context must not include protocol (http:// or https://)'
                );
            }
        }
    }
}
