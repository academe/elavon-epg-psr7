<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\CredentialOnFileType;
use Academe\Elavon\Epg\Psr7\Enums\ShopperInteraction;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * Stored Card data transfer object.
 *
 * Represents a card stored for a shopper that may be used for recurring payments.
 * Stored cards allow merchants to charge customers without requiring them to re-enter card details.
 *
 * Properties marked [Response] are typically only present in API responses.
 * Properties marked [Request] are typically sent in API requests.
 *
 * Note: Implements custom fromData() to handle enum conversion.
 */
class StoredCard implements DataTransferObject
{
    use SerializesData {
        fromData as private fromDataTrait;
    }

    // Normalized properties (objects)
    public readonly ?Card $card;

    /**
     * Get property type definitions for this DTO.
     *
     * @return array<string, array<string>>
     */
    public static function getPropertyTypes(): array
    {
        return [
            'object' => ['card'],
            'enum' => ['shopperInteraction', 'credentialOnFileType'],
            'string' => [
                'href', 'id', 'createdAt', 'modifiedAt', 'deletedAt',
                'merchant', 'shopper', 'hostedCard',
                'paymentMethodLink', 'paymentMethodSession',
                'customReference',
            ],
            'array' => ['customFields'],
        ];
    }

    /**
     * Creates a StoredCard instance from JSON-compatible data.
     *
     * @param mixed $data Array with stored card data
     * @return static
     * @throws InvalidArgumentException When data is invalid
     */
    public static function fromData(mixed $data): static
    {
        // Parse shopperInteraction enum if present
        $shopperInteraction = null;
        if (isset($data['shopperInteraction'])) {
            $shopperInteraction = ShopperInteraction::tryFrom($data['shopperInteraction']);
            if ($shopperInteraction === null) {
                throw new InvalidArgumentException("Invalid shopper interaction: {$data['shopperInteraction']}");
            }
        }

        // Parse credentialOnFileType enum if present
        $credentialOnFileType = null;
        if (isset($data['credentialOnFileType'])) {
            $credentialOnFileType = CredentialOnFileType::tryFrom($data['credentialOnFileType']);
            if ($credentialOnFileType === null) {
                throw new InvalidArgumentException("Invalid credential on file type: {$data['credentialOnFileType']}");
            }
        }

        return new self(
            card: $data['card'] ?? null,
            shopper: isset($data['shopper']) ? (string) $data['shopper'] : null,
            hostedCard: isset($data['hostedCard']) ? (string) $data['hostedCard'] : null,
            href: isset($data['href']) ? (string) $data['href'] : null,
            id: isset($data['id']) ? (string) $data['id'] : null,
            createdAt: isset($data['createdAt']) ? (string) $data['createdAt'] : null,
            modifiedAt: isset($data['modifiedAt']) ? (string) $data['modifiedAt'] : null,
            deletedAt: isset($data['deletedAt']) ? (string) $data['deletedAt'] : null,
            merchant: isset($data['merchant']) ? (string) $data['merchant'] : null,
            shopperInteraction: $shopperInteraction,
            credentialOnFileType: $credentialOnFileType,
            paymentMethodLink: isset($data['paymentMethodLink']) ? (string) $data['paymentMethodLink'] : null,
            paymentMethodSession: isset($data['paymentMethodSession']) ? (string) $data['paymentMethodSession'] : null,
            customReference: isset($data['customReference']) ? (string) $data['customReference'] : null,
            customFields: $data['customFields'] ?? null,
        );
    }

    /**
     * @param Card|array<string, mixed>|null $card [Response] Card details
     * @param string|null $shopper [Request/Response] Shopper resource URL (required for creation)
     * @param string|null $hostedCard [Request] HostedCard resource URL (for initialization)
     * @param string|null $href [Response] Resource URL (self link)
     * @param string|null $id [Response] StoredCard ID assigned by server
     * @param string|null $createdAt [Response] Creation timestamp (ISO 8601)
     * @param string|null $modifiedAt [Response] Modification timestamp (ISO 8601)
     * @param string|null $deletedAt [Response] Deletion timestamp (ISO 8601)
     * @param string|null $merchant [Response] Merchant resource URL
     * @param ShopperInteraction|null $shopperInteraction [Response] Shopper interaction type
     * @param CredentialOnFileType|null $credentialOnFileType [Response] Credential on file type
     * @param string|null $paymentMethodLink Payment method link resource URL
     * @param string|null $paymentMethodSession Payment method session resource URL
     * @param string|null $customReference Optional merchant reference (max 255 chars)
     * @param array<string, string>|null $customFields Custom fields (key-value pairs)
     *
     * @throws InvalidArgumentException When validation fails
     */
    public function __construct(
        Card|array|null $card = null,
        public readonly ?string $shopper = null,
        public readonly ?string $hostedCard = null,
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $modifiedAt = null,
        public readonly ?string $deletedAt = null,
        public readonly ?string $merchant = null,
        public readonly ?ShopperInteraction $shopperInteraction = null,
        public readonly ?CredentialOnFileType $credentialOnFileType = null,
        public readonly ?string $paymentMethodLink = null,
        public readonly ?string $paymentMethodSession = null,
        public readonly ?string $customReference = null,
        public readonly ?array $customFields = null,
    ) {
        // Normalize Card
        $this->card = match (true) {
            $card instanceof Card => $card,
            is_array($card) => Card::fromData($card),
            default => null,
        };

        $this->validate();
    }

    /**
     * Validates stored card data.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // Validate custom reference length
        if ($this->customReference !== null && strlen($this->customReference) > 255) {
            throw new InvalidArgumentException('Custom reference must not exceed 255 characters');
        }

        // Validate custom fields
        if ($this->customFields !== null) {
            foreach ($this->customFields as $key => $value) {
                if (strlen($key) > 64) {
                    throw new InvalidArgumentException('Custom field name must not exceed 64 characters');
                }
                if (strlen($value) > 1024) {
                    throw new InvalidArgumentException('Custom field value must not exceed 1024 characters');
                }
            }
        }
    }
}
