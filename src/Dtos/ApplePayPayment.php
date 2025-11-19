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

    // Normalized nested DTOs
    public readonly ?Card $card;
    public readonly ?VerificationResults $verificationResults;

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

    /**
     * @param string|null $href ApplePayPayment Resource URL (self link) [Response]
     * @param string|null $id ApplePayPayment Resource ID [Response]
     * @param string|null $createdAt Creation timestamp [Response]
     * @param string|null $expiresAt Expiration timestamp calculated from the Apple Pay token signing time [Response]
     * @param string|null $merchant Merchant Resource URL [Response]
     * @param string|null $processorAccount ProcessorAccount Resource URL [Response]
     * @param string|null $account Account Resource URL [Request]
     * @param string|null $token The Apple transaction id, encrypted payment data and data elements used for decryption [Request]
     * @param Card|array<string, mixed>|null $card Card data (only Contact billTo info can be provided alongside the token)
     * @param string|null $customReference Custom reference
     * @param array<string, mixed>|null $customFields Custom fields
     * @param VerificationResults|array<string, mixed>|null $verificationResults Verification results [Response]
     */
    public function __construct(
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $expiresAt = null,
        public readonly ?string $merchant = null,
        public readonly ?string $processorAccount = null,
        public readonly ?string $account = null,
        public readonly ?string $token = null,
        Card|array|null $card = null,
        public readonly ?string $customReference = null,
        public readonly ?array $customFields = null,
        VerificationResults|array|null $verificationResults = null,
    ) {
        // Normalize Card object
        $this->card = match (true) {
            $card instanceof Card => $card,
            is_array($card) => Card::fromData($card),
            default => null,
        };

        // Normalize VerificationResults object
        $this->verificationResults = match (true) {
            $verificationResults instanceof VerificationResults => $verificationResults,
            is_array($verificationResults) => VerificationResults::fromData($verificationResults),
            default => null,
        };
    }
}
