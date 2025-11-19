<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;

/**
 * Paze Payment data transfer object.
 *
 * This captures all the card information that is encrypted within a transaction that uses PAZE as
 * a form of payment. This cannot be used in conjunction with an ApplePay payment or a GooglePay payment.
 * Pass in the encrypted PAZE string in the token variable and get the decrypted value in the form of
 * a card object.
 *
 * All properties are read-only.
 */
class PazePayment implements DataTransferObject
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
            'string' => [
                'href', 'id', 'createdAt', 'expiresAt', 'merchant', 'processorAccount',
                'account', 'token', 'payloadId', 'sessionId', 'customReference',
            ],
            'array' => ['customFields'],
        ];
    }

    /**
     * @param string|null $href PazePayment Resource URL (self link) [Response]
     * @param string|null $id PazePayment Resource ID [Response]
     * @param string|null $createdAt Creation timestamp [Response]
     * @param string|null $expiresAt Expiration timestamp [Response]
     * @param string|null $merchant Merchant Resource URL [Response]
     * @param string|null $processorAccount ProcessorAccount Resource URL [Response]
     * @param string|null $account Account Resource URL [Request]
     * @param string|null $token The encrypted PAZE payment string [Request]
     * @param Card|array<string, mixed>|null $card Card data (only Contact billTo info can be provided alongside the token)
     * @param string|null $payloadId Alphanumeric string returned from PAZE that refers to the current token and sessionId [Request]
     * @param string|null $sessionId Alphanumeric string returned from PAZE that refers to the current token and payloadId [Request]
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
        public readonly ?string $payloadId = null,
        public readonly ?string $sessionId = null,
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
