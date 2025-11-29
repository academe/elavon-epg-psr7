<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\HppType;
use Academe\Elavon\Epg\Psr7\Enums\PaymentMethod;
use Academe\Elavon\Epg\Psr7\Enums\PaymentMethodOrigin;
use Academe\Elavon\Epg\Psr7\Enums\ShopperInteraction;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Money\Money;

/**
 * PaymentSession data transfer object.
 *
 * A payment session securely collects payment details from the shopper using
 * the hosted payment page and sends them directly to the platform, allowing
 * the merchant to minimize PCI DSS scope.
 *
 * All properties are optional as different fields are used in different contexts.
 * Validation of required fields should occur at the message level (CreatePaymentSessionRequest, etc.).
 *
 * Properties marked [Response] are typically only present in API responses.
 * Properties marked [Request] are typically sent in API requests.
 * Properties without markers may appear in both contexts.
 */
class PaymentSession implements DataTransferObject
{
    use SerializesData;

    /** @var array<PaymentMethod>|null */
    public readonly ?array $allowedPaymentMethods;

    /** @var array<PaymentMethodOrigin>|null */
    public readonly ?array $allowedPaymentMethodOrigins;

    /** @var array<Transaction>|null */
    public readonly ?array $previousTransactions;

    /**
     * Get property type definitions for this DTO.
     *
     * @return array<string, array<string>>
     */
    public static function getPropertyTypes(): array
    {
        return [
            'money' => ['salesTax', 'tip'],
            'object' => ['billTo', 'shipTo', 'blik', 'debtorAccount', 'threeDSecure'],
            'array' => ['allowedPaymentMethods', 'allowedPaymentMethodOrigins', 'previousTransactions', 'customFields'],
            'string' => [
                'href', 'id', 'createdAt', 'modifiedAt', 'expiresAt', 'merchant', 'account', 'url',
                'order', 'paymentLink', 'forexAdvice', 'surchargeAdvice', 'transaction',
                'hostedCard', 'storedCard', 'hostedAchPayment', 'storedAchPayment',
                'googlePayPayment', 'applePayPayment', 'pazePayment', 'shopper',
                'shopperEmailAddress', 'returnUrl', 'cancelUrl', 'originUrl',
                'defaultLanguageTag', 'shopperLanguageTag', 'createdBy', 'customReference',
            ],
            'enum' => ['hppType', 'shopperInteraction'],
            'boolean' => ['doCreateTransaction', 'doCapture', 'doThreeDSecure', 'doReset', 'useStoredPaymentMethod'],
        ];
    }

    /**
     * @param PaymentMethod[] $allowedPaymentMethods
     * @param PaymentMethodOrigin[] $allowedPaymentMethodOrigins
     * @param Transaction[] $previousTransactions
     */
    public function __construct(
        // Response-only fields
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $modifiedAt = null,
        public readonly ?string $merchant = null,
        public readonly ?string $url = null,

        // Request/Response fields
        public readonly ?string $expiresAt = null,
        public readonly ?string $account = null,
        public readonly ?string $order = null,
        public readonly ?string $paymentLink = null,
        public readonly ?Money $salesTax = null,
        public readonly ?Money $tip = null,
        public readonly ?string $forexAdvice = null,
        public readonly ?string $surchargeAdvice = null,
        public readonly ?string $transaction = null,
        public readonly ?string $hostedCard = null,
        public readonly ?string $storedCard = null,
        public readonly ?string $hostedAchPayment = null,
        public readonly ?string $storedAchPayment = null,
        public readonly ?string $googlePayPayment = null,
        public readonly ?string $applePayPayment = null,
        public readonly ?string $pazePayment = null,
        public readonly ?Blik $blik = null,
        public readonly ?string $shopper = null,
        public readonly ?DebtorAccount $debtorAccount = null,
        public readonly ?ThreeDSecure $threeDSecure = null,
        public readonly ?string $shopperEmailAddress = null,
        public readonly ?Contact $billTo = null,
        public readonly ?Contact $shipTo = null,
        public readonly ?HppType $hppType = null,
        public readonly ?string $returnUrl = null,
        public readonly ?string $cancelUrl = null,
        public readonly ?string $originUrl = null,
        public readonly ?string $defaultLanguageTag = null,
        public readonly ?string $shopperLanguageTag = null,
        public readonly ?ShopperInteraction $shopperInteraction = null,
        public readonly ?bool $doCreateTransaction = null,
        public readonly ?bool $doCapture = null,
        public readonly ?bool $doThreeDSecure = null,
        public readonly ?bool $doReset = null,
        public readonly ?bool $useStoredPaymentMethod = null,
        public readonly ?string $createdBy = null,
        ?array $previousTransactions = null,
        public readonly ?string $customReference = null,
        public readonly ?array $customFields = null,
        ?array $allowedPaymentMethods = null,
        ?array $allowedPaymentMethodOrigins = null,
    ) {
        // Normalize allowedPaymentMethods array
        if ($allowedPaymentMethods !== null) {
            $this->allowedPaymentMethods = array_map(
                fn($method) => $method instanceof PaymentMethod
                    ? $method
                    : PaymentMethod::from($method),
                $allowedPaymentMethods
            );
        } else {
            $this->allowedPaymentMethods = null;
        }

        // Normalize allowedPaymentMethodOrigins array
        if ($allowedPaymentMethodOrigins !== null) {
            $this->allowedPaymentMethodOrigins = array_map(
                fn($origin) => $origin instanceof PaymentMethodOrigin
                    ? $origin
                    : PaymentMethodOrigin::from($origin),
                $allowedPaymentMethodOrigins
            );
        } else {
            $this->allowedPaymentMethodOrigins = null;
        }

        // Normalize previousTransactions array
        if ($previousTransactions !== null) {
            $this->previousTransactions = array_map(
                fn($transaction) => $transaction instanceof Transaction
                    ? $transaction
                    : Transaction::fromData($transaction),
                $previousTransactions
            );
        } else {
            $this->previousTransactions = null;
        }

        $this->validate();
    }

    /**
     * Validates payment session data.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // Validate shopperEmailAddress length
        if ($this->shopperEmailAddress !== null && strlen($this->shopperEmailAddress) > 254) {
            throw new InvalidArgumentException('Shopper email address must not exceed 254 characters');
        }

        // Validate returnUrl length and pattern
        if ($this->returnUrl !== null) {
            if (strlen($this->returnUrl) > 2048) {
                throw new InvalidArgumentException('Return URL must not exceed 2048 characters');
            }
            if (!preg_match('/^https?:\/\/[^\/]{2,}.*/', $this->returnUrl)) {
                throw new InvalidArgumentException('Return URL must be a valid HTTP/HTTPS URL');
            }
        }

        // Validate cancelUrl length and pattern
        if ($this->cancelUrl !== null) {
            if (strlen($this->cancelUrl) > 2048) {
                throw new InvalidArgumentException('Cancel URL must not exceed 2048 characters');
            }
            if (!preg_match('/^https?:\/\/[^\/]{2,}.*/', $this->cancelUrl)) {
                throw new InvalidArgumentException('Cancel URL must be a valid HTTP/HTTPS URL');
            }
        }

        // Validate originUrl length and pattern
        if ($this->originUrl !== null) {
            if (strlen($this->originUrl) > 2048) {
                throw new InvalidArgumentException('Origin URL must not exceed 2048 characters');
            }
            if (!preg_match('/^https?:\/\/[^\/]{2,}.*/', $this->originUrl)) {
                throw new InvalidArgumentException('Origin URL must be a valid HTTP/HTTPS URL');
            }
        }

        // Validate defaultLanguageTag length
        if ($this->defaultLanguageTag !== null && strlen($this->defaultLanguageTag) > 255) {
            throw new InvalidArgumentException('Default language tag must not exceed 255 characters');
        }

        // Validate shopperLanguageTag length
        if ($this->shopperLanguageTag !== null && strlen($this->shopperLanguageTag) > 255) {
            throw new InvalidArgumentException('Shopper language tag must not exceed 255 characters');
        }

        // Validate createdBy length
        if ($this->createdBy !== null && strlen($this->createdBy) > 255) {
            throw new InvalidArgumentException('Created by must not exceed 255 characters');
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
