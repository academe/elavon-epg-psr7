<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;

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

    /**
     * Get property type definitions for this DTO.
     *
     * @return array<string, array<string>>
     */
    public static function getPropertyTypes(): array
    {
        return [
            'object' => ['card', 'verificationResults'],
            'string' => ['href', 'id', 'createdAt', 'expiresAt', 'merchant', 'processorAccount', 'account', 'token', 'customReference'],
            'array' => ['customFields'],
        ];
    }

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
        public readonly ?array $customFields = null,
        public readonly ?VerificationResults $verificationResults = null,
    ) {
    }
}
