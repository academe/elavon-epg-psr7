<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\HppType;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * PaymentMethodSession data transfer object.
 *
 * A payment session securely collects payment details from the shopper using the hosted payment page
 * and sends them directly to the platform, allowing the merchant to minimize PCI DSS scope.
 *
 * Properties marked [Response] are typically only present in API responses.
 * Properties marked [Request] are typically sent in API requests.
 * Properties without markers may appear in both contexts.
 */
class PaymentMethodSession implements DataTransferObject
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
            'object' => ['threeDSecure', 'billTo'],
            'enum' => ['hppType'],
            'array' => ['customFields'],
            'string' => [
                'href', 'id', 'createdAt', 'modifiedAt', 'expiresAt',
                'merchant', 'account', 'url', 'paymentMethodLink', 'storedCard',
                'shopper', 'returnUrl', 'cancelUrl', 'originUrl',
                'defaultLanguageTag', 'shopperLanguageTag', 'customReference',
            ],
            'bool' => ['doThreeDSecure'],
        ];
    }

    /**
     * @param string|null $href [Response] PaymentMethodSession Resource URL (self link)
     * @param string|null $id [Response] Unique identifier assigned by server
     * @param string|null $createdAt [Response] Creation timestamp
     * @param string|null $modifiedAt [Response] Modification timestamp
     * @param string|null $expiresAt An expiration timestamp
     * @param string|null $merchant [Response] Merchant Resource URL
     * @param string|null $account Account Resource URL, defaults to merchant
     * @param string|null $url [Response] URL that shoppers will use
     * @param string|null $paymentMethodLink Payment Method Link Resource URL
     * @param string|null $storedCard StoredCard Resource URL
     * @param string|null $shopper [Response] Shopper Resource URL
     * @param HppType|string|null $hppType [Response] Indicates the type of hosted payments page, defaults to fullPageRedirect
     * @param string|null $returnUrl URL to redirect to after payment details are collected (max 2048 chars)
     * @param string|null $cancelUrl URL to redirect to if shopper cancels (max 2048 chars)
     * @param string|null $originUrl Origin where the HPP will be embedded. Required if using the lightbox (max 2048 chars)
     * @param string|null $defaultLanguageTag Default IETF language tag (max 255 chars)
     * @param string|null $shopperLanguageTag The IETF language tag optionally chosen by the shopper (max 255 chars)
     * @param bool|null $doThreeDSecure Determines whether or not the HPP will perform 3-D secure validation
     * @param ThreeDSecure|array<string, mixed>|null $threeDSecure Additional data only needed for 3-D Secure version 2 processing
     * @param Contact|array<string, mixed>|null $billTo Billing contact details to be used by default for the hosted card
     * @param string|null $customReference Optional reference provided by the merchant (max 255 chars)
     * @param array<string, string>|null $customFields Custom fields, an object containing arbitrary string values (field names max 64 chars, values max 1024 chars)
     *
     * @throws InvalidArgumentException When validation fails
     */
    public function __construct(
        // Response-only fields
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $modifiedAt = null,
        public readonly ?string $url = null,
        public readonly ?string $merchant = null,
        public readonly ?string $shopper = null,

        // Request/Response fields
        public readonly ?string $expiresAt = null,
        public readonly ?string $account = null,
        public readonly ?string $paymentMethodLink = null,
        public readonly ?string $storedCard = null,
        public readonly ?HppType $hppType = null,
        public readonly ?string $returnUrl = null,
        public readonly ?string $cancelUrl = null,
        public readonly ?string $originUrl = null,
        public readonly ?string $defaultLanguageTag = null,
        public readonly ?string $shopperLanguageTag = null,
        public readonly ?bool $doThreeDSecure = null,
        public readonly ?ThreeDSecure $threeDSecure = null,
        public readonly ?Contact $billTo = null,
        public readonly ?string $customReference = null,
        public readonly ?array $customFields = null,
    ) {
        $this->validate();
    }

    /**
     * Validates payment method session data.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // Validate URL lengths and patterns
        $urlFields = ['returnUrl', 'cancelUrl', 'originUrl'];
        foreach ($urlFields as $field) {
            if ($this->$field !== null) {
                if (strlen($this->$field) > 2048) {
                    throw new InvalidArgumentException("{$field} must not exceed 2048 characters");
                }
                if (!preg_match('#^https?://[^/]{2,}.*$#', $this->$field)) {
                    throw new InvalidArgumentException("{$field} must be a valid http or https URL");
                }
            }
        }

        // Validate language tag lengths
        if ($this->defaultLanguageTag !== null && strlen($this->defaultLanguageTag) > 255) {
            throw new InvalidArgumentException('Default language tag must not exceed 255 characters');
        }

        if ($this->shopperLanguageTag !== null && strlen($this->shopperLanguageTag) > 255) {
            throw new InvalidArgumentException('Shopper language tag must not exceed 255 characters');
        }

        // Validate customReference length
        if ($this->customReference !== null && strlen($this->customReference) > 255) {
            throw new InvalidArgumentException('Custom reference must not exceed 255 characters');
        }

        // Validate customFields
        if ($this->customFields !== null) {
            foreach ($this->customFields as $key => $value) {
                if (strlen($key) > 64) {
                    throw new InvalidArgumentException('Custom field names must not exceed 64 characters');
                }
                if (strlen($value) > 1024) {
                    throw new InvalidArgumentException('Custom field values must not exceed 1024 characters');
                }
            }
        }
    }
}
