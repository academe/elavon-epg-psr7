<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\ValueObjects\CustomFields;

/**
 * Apple Pay Payment data transfer object.
 *
 * This captures all of the components necessary for processing a payment with the Apple Pay digital wallet.
 * The payment token contains the encrypted payment data needed for transaction processing along with the
 * elements needed for decryption. This cannot be used in conjunction with a Google Pay payment.
 *
 * All properties are read-only.
 */
class ApplePayPayment implements DataTransferObject
{
    use SerializesData;

    public function __construct(
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $expiresAt = null,
        public readonly ?string $merchant = null,
        public readonly ?string $processorAccount = null,
        public readonly ?string $account = null,
        public readonly ?string $token = null,
        public readonly ?Card $card = null,
        public readonly ?string $customReference = null,
        public readonly ?CustomFields $customFields = null,
        public readonly ?VerificationResults $verificationResults = null,
    ) {
    }
}
