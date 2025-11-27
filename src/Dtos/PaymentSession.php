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

    // Normalized properties (objects)
    public readonly ?Contact $billTo;
    public readonly ?Contact $shipTo;
    public readonly ?Blik $blik;
    public readonly ?DebtorAccount $debtorAccount;
    public readonly ?ThreeDSecure $threeDSecure;
    // public readonly ?HppType $hppType;
    // public readonly ?ShopperInteraction $shopperInteraction;

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
     * @param string|null $href [Response] PaymentSession Resource URL (self link)
     * @param string|null $id [Response] PaymentSession Resource ID assigned by server
     * @param string|null $createdAt [Response] Creation timestamp
     * @param string|null $modifiedAt [Response] Modification timestamp
     * @param string|null $expiresAt Expiration timestamp
     * @param string|null $merchant [Response] Merchant Resource URL
     * @param string|null $account Account Resource URL (defaults to merchant)
     * @param string|null $url [Response] URL that shoppers will use
     * @param string|null $order Order Resource URL for which payment is being requested (required for creation)
     * @param array<string>|null $allowedPaymentMethods [Response] Payment methods allowed to be shown in the hosted payments page
     * @param array<string>|null $allowedPaymentMethodOrigins [Response] Allowed origins of the payment methods listed in allowedPaymentMethods
     * @param string|null $paymentLink PaymentLink Resource URL
     * @param Money|null $salesTax Sales Tax
     * @param Money|null $tip [Response] Tip
     * @param string|null $forexAdvice ForexAdvice Resource URL
     * @param string|null $surchargeAdvice SurchargeAdvice Resource URL
     * @param string|null $transaction Transaction Resource URL
     * @param string|null $hostedCard HostedCard Resource URL
     * @param string|null $storedCard StoredCard Resource URL
     * @param string|null $hostedAchPayment HostedAchPayment Resource URL
     * @param string|null $storedAchPayment StoredAchPayment Resource URL
     * @param string|null $googlePayPayment GooglePayPayment obtained through the create Google Pay payment API call
     * @param string|null $applePayPayment ApplePayPayment obtained through the create Apple Pay payment API call
     * @param string|null $pazePayment PazePayment obtained through the create Paze payment API call
     * @param Blik|array<string, mixed>|null $blik BLIK payment code
     * @param string|null $shopper Shopper Resource URL
     * @param DebtorAccount|array<string, mixed>|null $debtorAccount Account information required for MCC 6012/6050/6051 merchants
     * @param ThreeDSecure|array<string, mixed>|null $threeDSecure Additional data for 3-D Secure version 2 processing
     * @param string|null $shopperEmailAddress Shopper's email address (max 254 chars)
     * @param Contact|array<string, mixed>|null $billTo Billing contact details for the hosted card
     * @param Contact|array<string, mixed>|null $shipTo Shipping contact details
     * @param HppType|string|null $hppType [Response] Hosted payments page type (defaults to fullPageRedirect)
     * @param string|null $returnUrl URL to redirect to after payment details are collected (max 2048 chars, required for hppType = fullPageRedirect)
     * @param string|null $cancelUrl URL to redirect to if shopper cancels (max 2048 chars, required for hppType = fullPageRedirect)
     * @param string|null $originUrl Origin where hosted payment page will be embedded (max 2048 chars, required for lightbox)
     * @param string|null $defaultLanguageTag [Response] Default IETF language tag for HPP (max 255 chars)
     * @param string|null $shopperLanguageTag IETF language tag chosen by shopper in HPP (max 255 chars)
     * @param ShopperInteraction|string|null $shopperInteraction Shopper interaction type
     * @param bool|null $doCreateTransaction [Response] Whether HPP will perform end-to-end transaction (defaults to false)
     * @param bool|null $doCapture [Response] Whether to capture transaction (defaults to true)
     * @param bool|null $doThreeDSecure [Response] Whether HPP will perform 3-D Secure validation
     * @param bool|null $doReset Whether HPP will be reset
     * @param bool|null $useStoredPaymentMethod [Response] Whether to force use of stored payment method
     * @param string|null $createdBy Who or what created the payment session (max 255 chars)
     * @param array<array<string, mixed>>|null $previousTransactions [Response] Previous transactions
     * @param string|null $customReference Optional reference provided by merchant (max 255 chars)
     * @param array<string, string>|null $customFields Custom fields (field names max 64 chars, values max 1024 chars)
     *
     * @throws InvalidArgumentException When validation fails
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
        Blik|array|null $blik = null,
        public readonly ?string $shopper = null,
        DebtorAccount|array|null $debtorAccount = null,
        ThreeDSecure|array|null $threeDSecure = null,
        public readonly ?string $shopperEmailAddress = null,
        Contact|array|null $billTo = null,
        Contact|array|null $shipTo = null,
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
        // Normalize Contact objects
        $this->billTo = match (true) {
            $billTo instanceof Contact => $billTo,
            is_array($billTo) => Contact::fromData($billTo),
            default => null,
        };

        $this->shipTo = match (true) {
            $shipTo instanceof Contact => $shipTo,
            is_array($shipTo) => Contact::fromData($shipTo),
            default => null,
        };

        // Normalize Blik object
        $this->blik = match (true) {
            $blik instanceof Blik => $blik,
            is_array($blik) => Blik::fromData($blik),
            default => null,
        };

        // Normalize DebtorAccount object
        $this->debtorAccount = match (true) {
            $debtorAccount instanceof DebtorAccount => $debtorAccount,
            is_array($debtorAccount) => DebtorAccount::fromData($debtorAccount),
            default => null,
        };

        // Normalize ThreeDSecure object
        $this->threeDSecure = match (true) {
            $threeDSecure instanceof ThreeDSecure => $threeDSecure,
            is_array($threeDSecure) => ThreeDSecure::fromData($threeDSecure),
            default => null,
        };

        // // Normalize HppType enum
        // $this->hppType = match (true) {
        //     $hppType instanceof HppType => $hppType,
        //     is_string($hppType) => HppType::from($hppType),
        //     default => null,
        // };

        // // Normalize ShopperInteraction enum
        // $this->shopperInteraction = match (true) {
        //     $shopperInteraction instanceof ShopperInteraction => $shopperInteraction,
        //     is_string($shopperInteraction) => ShopperInteraction::from($shopperInteraction),
        //     default => null,
        // };

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
