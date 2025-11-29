<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;

/**
 * Google Pay Payment data transfer object.
 *
 * This captures all the card information that is encrypted within a transaction that uses Google Pay
 * as a form of payment. This cannot be used in conjunction with an Apple Pay payment.
 * Pass in the encrypted Google Pay string in the token variable and get the decrypted value in the
 * form of a card object.
 *
 * All properties are read-only.
 */
class GooglePayPayment implements DataTransferObject
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
