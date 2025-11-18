# Elavon EPG PSR-7 API Implementation Plan

This document provides a comprehensive plan for completing the API messages package according to the OpenAPI specification (version 2025-10-01).

## Table of Contents

1. [Current State](#current-state)
2. [Architecture Patterns](#architecture-patterns)
3. [Resource Coverage Plan](#resource-coverage-plan)
4. [DTOs and Value Objects](#dtos-and-value-objects)
5. [Enums](#enums)
6. [Validation Strategy](#validation-strategy)
7. [Implementation Phases](#implementation-phases)
8. [Priority Classification](#priority-classification)

---

## Current State

### Existing Messages

**Request Messages:**
- `CreateTransactionRequest` - Creates PSR-7 POST /transactions request

**Response Messages:**
- `TransactionResponse` - Parses transaction response with error handling

**Response Concerns:**
- `HandlesErrors` - Trait for error detection and parsing

### Existing DTOs

| DTO | Properties | Status |
|-----|-----------|--------|
| **Transaction** | ~70 properties (Money, Card, enums, nested DTOs) | ✅ Complete |
| **Card** | Pan, CVV, expiry, holder name, scheme | ✅ Complete |
| **Money** | Amount, currency code | ✅ Complete |
| **Failure** | Code, description, field | ✅ Complete |
| **Contact** | Name, company, address fields, phones, email | ✅ Complete |
| **ShopperStatement** | Name, phone, URL (with validation) | ✅ Complete |
| **Surcharge** | 3 Money objects + rate | ✅ Complete |
| **ThreeDSecure** | 3DS v2 authentication data | ✅ Complete |
| **ErrorResponse** | HTTP error response structure | ✅ Complete |
| **ErrorDetail** | Individual error details | ✅ Complete |

### Existing Enums

| Enum | Values | Status |
|------|--------|--------|
| **TransactionState** | declined, authorized, authorizationPending, captured, settled, reversed | ✅ Complete |
| **TransactionType** | sale, refund, void | ✅ Complete |
| **PaymentMethod** | Card, BLIK, ACH | ✅ Complete |
| **PaymentMethodOrigin** | Card, Apple Pay, Google Pay, Paze, BLIK, etc. | ✅ Complete |
| **PaymentMethodQualifier** | credit, debit | ✅ Complete |
| **ProcessorDirective** | none, reversal | ✅ Complete |
| **Source** | directApiCall, hppSubmitRedirect, etc. (12 values) | ✅ Complete |
| **MarketSegment** | retail, restaurant | ✅ Complete |
| **ShopperInteraction** | ecommerce, mailOrder, telephoneOrder, etc. | ✅ Complete |
| **MarkupRateAnnotation** | none, aboveEcb, belowEcb | ✅ Complete |
| **Currency** | ISO 4217 currency codes | ✅ Complete |
| **CardScheme** | Visa, Mastercard, Amex, Discover, etc. | ✅ Complete |

### Existing Value Objects

| Value Object | Purpose | Status |
|--------------|---------|--------|
| **Money** | Amount + currency with validation | ✅ Complete |

---

## Architecture Patterns

### Request Message Pattern

```php
class Create{Resource}Request
{
    private const DEFAULT_REQUIRED_FIELDS = ['field1', 'field2'];

    public function __construct(
        private readonly {Resource}|array $data,
        private readonly ?array $requiredFields = null,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
        private readonly string $baseUri = 'https://api.eu.elavonpayments.com',
    ) {}

    public function build(): RequestInterface
    {
        // 1. Normalize to DTO object
        // 2. Validate required fields
        // 3. Serialize to JSON
        // 4. Build PSR-7 request with method/path/headers
    }

    private function validate{Resource}Request({Resource} $resource): void
    {
        // Validate required fields for this specific request
    }

    public function get{Resource}(): {Resource}
    {
        // Return normalized DTO
    }
}
```

### Response Message Pattern

```php
class {Resource}Response
{
    use HandlesErrors;

    private readonly ?{Resource} $resource;

    public function __construct(
        private readonly ResponseInterface $response,
    ) {
        if ($this->isSuccessful()) {
            $this->resource = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->resource = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    public static function fromPsr7Response(ResponseInterface $response): self
    {
        return new self($response);
    }

    public function get{Resource}(): ?{Resource}
    {
        return $this->resource;
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function getPsr7Response(): ResponseInterface
    {
        return $this->response;
    }

    private function parseSuccessResponse(): {Resource}
    {
        $data = $this->parseJsonBody();
        return {Resource}::fromArray($data);
    }

    private function parseJsonBody(): array
    {
        // Parse and validate JSON body
    }
}
```

### Update Request Pattern

```php
class Update{Resource}Request
{
    // Similar to Create but:
    // - Uses PATCH method instead of POST
    // - Includes resource ID in URL path
    // - All fields are optional (partial updates)
    // - No required fields validation
}
```

### List Response Pattern

```php
class {Resource}ListResponse
{
    use HandlesErrors;

    private readonly ?array $items; // array<{Resource}>
    private readonly ?string $nextPage;
    private readonly ?string $firstPage;

    public function __construct(
        private readonly ResponseInterface $response,
    ) {
        if ($this->isSuccessful()) {
            $data = $this->parseSuccessResponse();
            $this->items = $data['items'];
            $this->nextPage = $data['next'] ?? null;
            $this->firstPage = $data['first'] ?? null;
            $this->error = null;
        } else {
            $this->items = null;
            $this->nextPage = null;
            $this->firstPage = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    public function getItems(): ?array
    {
        return $this->items;
    }

    public function getNextPage(): ?string
    {
        return $this->nextPage;
    }

    public function getFirstPage(): ?string
    {
        return $this->firstPage;
    }
}
```

---

## Resource Coverage Plan

### Priority 1: Core Transaction Resources (IMMEDIATE)

These are essential for basic payment processing:

| Resource | Operations | Messages Needed |
|----------|-----------|-----------------|
| **Transactions** | Create, Retrieve, Update, List | ✅ CreateTransactionRequest<br>✅ TransactionResponse<br>❌ UpdateTransactionRequest<br>❌ TransactionListResponse |
| **HostedCards** | Create, Retrieve, Update | ❌ CreateHostedCardRequest<br>❌ HostedCardResponse<br>❌ UpdateHostedCardRequest |
| **ForexAdvices** | Create, Retrieve | ❌ CreateForexAdviceRequest<br>❌ ForexAdviceResponse |
| **SurchargeAdvices** | Create, Retrieve | ❌ CreateSurchargeAdviceRequest<br>❌ SurchargeAdviceResponse |

### Priority 2: Payment Methods (HIGH)

Support for various payment methods:

| Resource | Operations | Messages Needed |
|----------|-----------|-----------------|
| **ApplePayPayments** | Create, Retrieve | ❌ CreateApplePayPaymentRequest<br>❌ ApplePayPaymentResponse |
| **GooglePayPayments** | Create, Retrieve | ❌ CreateGooglePayPaymentRequest<br>❌ GooglePayPaymentResponse |
| **PazePayments** | Create, Retrieve | ❌ CreatePazePaymentRequest<br>❌ PazePaymentResponse |
| **HostedAchPayments** | Create, Retrieve | ❌ CreateHostedAchPaymentRequest<br>❌ HostedAchPaymentResponse |
| **HsmCards** | Create, Retrieve | ❌ CreateHsmCardRequest<br>❌ HsmCardResponse |

### Priority 3: Stored Payment Methods (HIGH)

For recurring payments and tokenization:

| Resource | Operations | Messages Needed |
|----------|-----------|-----------------|
| **StoredCards** | Create, Retrieve, Update, Delete | ❌ CreateStoredCardRequest<br>❌ StoredCardResponse<br>❌ UpdateStoredCardRequest<br>❌ DeleteStoredCardRequest |
| **StoredAchPayments** | Create, Retrieve, Update, Delete | ❌ CreateStoredAchPaymentRequest<br>❌ StoredAchPaymentResponse<br>❌ UpdateStoredAchPaymentRequest<br>❌ DeleteStoredAchPaymentRequest |
| **Shoppers** | Create, Retrieve, Update, Delete, List | ❌ CreateShopperRequest<br>❌ ShopperResponse<br>❌ UpdateShopperRequest<br>❌ DeleteShopperRequest<br>❌ ShopperListResponse |

### Priority 4: Payment Sessions & Links (MEDIUM)

For hosted payment pages:

| Resource | Operations | Messages Needed |
|----------|-----------|-----------------|
| **PaymentSessions** | Create, Retrieve, Update | ❌ CreatePaymentSessionRequest<br>❌ PaymentSessionResponse<br>❌ UpdatePaymentSessionRequest |
| **PaymentLinks** | Create, Retrieve, Update, List | ❌ CreatePaymentLinkRequest<br>❌ PaymentLinkResponse<br>❌ UpdatePaymentLinkRequest<br>❌ PaymentLinkListResponse |
| **PaymentMethodLinks** | Create, Retrieve, Update, List | ❌ CreatePaymentMethodLinkRequest<br>❌ PaymentMethodLinkResponse<br>❌ UpdatePaymentMethodLinkRequest<br>❌ PaymentMethodLinkListResponse |
| **PaymentMethodSessions** | Create, Retrieve, Update, List | ❌ CreatePaymentMethodSessionRequest<br>❌ PaymentMethodSessionResponse<br>❌ UpdatePaymentMethodSessionRequest<br>❌ PaymentMethodSessionListResponse |
| **PaymentLinkEvents** | Create, Retrieve, List | ❌ CreatePaymentLinkEventRequest<br>❌ PaymentLinkEventResponse<br>❌ PaymentLinkEventListResponse |
| **ApplePayPaymentSessions** | Create | ❌ CreateApplePayPaymentSessionRequest<br>❌ ApplePayPaymentSessionResponse |

### Priority 5: Subscriptions & Plans (MEDIUM)

For recurring billing:

| Resource | Operations | Messages Needed |
|----------|-----------|-----------------|
| **Plans** | Create, Retrieve, Update, Delete, List | ❌ CreatePlanRequest<br>❌ PlanResponse<br>❌ UpdatePlanRequest<br>❌ DeletePlanRequest<br>❌ PlanListResponse |
| **Subscriptions** | Create, Retrieve, Update, List | ❌ CreateSubscriptionRequest<br>❌ SubscriptionResponse<br>❌ UpdateSubscriptionRequest<br>❌ SubscriptionListResponse |

### Priority 6: Batch Processing (MEDIUM)

For settlement management:

| Resource | Operations | Messages Needed |
|----------|-----------|-----------------|
| **Batches** | Retrieve, List | ❌ BatchResponse<br>❌ BatchListResponse |
| **ManualBatches** | Create, Retrieve, Update, List | ❌ CreateManualBatchRequest<br>❌ ManualBatchResponse<br>❌ UpdateManualBatchRequest<br>❌ ManualBatchListResponse |
| **TotalAdjustments** | Create, Retrieve, List | ❌ CreateTotalAdjustmentRequest<br>❌ TotalAdjustmentResponse<br>❌ TotalAdjustmentListResponse |

### Priority 7: Orders (MEDIUM)

For order management:

| Resource | Operations | Messages Needed |
|----------|-----------|-----------------|
| **Orders** | Create, Retrieve, Update, List | ❌ CreateOrderRequest<br>❌ OrderResponse<br>❌ UpdateOrderRequest<br>❌ OrderListResponse |

### Priority 8: Account & Configuration (LOW)

Read-only configuration resources:

| Resource | Operations | Messages Needed |
|----------|-----------|-----------------|
| **Merchants** | Retrieve, List | ❌ MerchantResponse<br>❌ MerchantListResponse |
| **Accounts** | Retrieve, List | ❌ AccountResponse<br>❌ AccountListResponse |
| **ProcessorAccounts** | Retrieve | ❌ ProcessorAccountResponse |
| **Terminals** | Retrieve, List | ❌ TerminalResponse<br>❌ TerminalListResponse |

### Priority 9: Notifications & Events (LOW)

For webhook/event handling:

| Resource | Operations | Messages Needed |
|----------|-----------|-----------------|
| **Notifications** | Retrieve, List | ❌ NotificationResponse<br>❌ NotificationListResponse |

### Priority 10: Specialized Operations (LOW)

Less commonly used:

| Resource | Operations | Messages Needed |
|----------|-----------|-----------------|
| **EmailReceiptRequests** | Create | ❌ CreateEmailReceiptRequest<br>❌ EmailReceiptResponse |
| **PanTokens** | Create | ❌ CreatePanTokensRequest<br>❌ PanTokensResponse |
| **RefundSurchargeAdvices** | Create, Retrieve | ❌ CreateRefundSurchargeAdviceRequest<br>❌ RefundSurchargeAdviceResponse |

---

## DTOs and Value Objects

### Priority 1: Payment DTOs (IMMEDIATE)

| DTO | Purpose | Properties | Status |
|-----|---------|-----------|--------|
| **HostedCard** | Hosted card details | Similar to Card + href, id, etc. | ❌ |
| **HsmCard** | HSM encrypted card | Encrypted data, terminal info | ❌ |
| **AchPayment** | ACH payment details | Routing number, account number, account type | ❌ |
| **HostedAchPayment** | Hosted ACH payment | Similar to AchPayment + hosted fields | ❌ |
| **StoredCard** | Stored card token | Card reference, shopper, expiry | ❌ |
| **StoredAchPayment** | Stored ACH token | ACH reference, shopper | ❌ |
| **ApplePayPayment** | Apple Pay data | Payment data, header, signature | ❌ |
| **GooglePayPayment** | Google Pay data | Payment token, signature | ❌ |
| **PazePayment** | Paze payment data | Paze-specific fields | ❌ |
| **Wallet** | Digital wallet details | Wallet type, token data | ❌ |

### Priority 2: Transaction Support DTOs (HIGH)

| DTO | Purpose | Properties | Status |
|-----|---------|-----------|--------|
| **ForexAdvice** | Currency conversion quote | Rates, amounts, expiry | ❌ |
| **SurchargeAdvice** | Surcharge calculation | Surcharge details, rates | ❌ |
| **RefundSurchargeAdvice** | Refund surcharge calculation | Refund surcharge details | ❌ |
| **PartialAuthorization** | Partial auth details | Authorized amount vs requested | ❌ |
| **Blik** | BLIK payment (Polish) | BLIK code, expiry | ❌ |
| **CredentialOnFileData** | Note: Actually just a string! | (No DTO needed - max 29 chars) | N/A |

### Priority 3: 3D Secure & Verification (HIGH)

| DTO | Purpose | Properties | Status |
|-----|---------|-----------|--------|
| **ThreeDSecure** | 3DS v2 authentication | Transaction ID, status, ECI, protocol version | ✅ Complete |
| **VerificationResults** | Card verification results | CVV, AVS, 3DS results | ❌ |
| **Verification** | Verification data | Security code, postal code | ❌ |

### Priority 4: Terminal & Device DTOs (MEDIUM)

| DTO | Purpose | Properties | Status |
|-----|---------|-----------|--------|
| **PointOfInteraction** | POS interaction details | Terminal type, capabilities | ❌ |
| **DeviceInteraction** | Device interaction mode | Online, offline, contactless | ❌ |
| **EncryptedData** | Encrypted payment data | Encrypted value, KSN, format | ❌ |
| **PinEncryptedData** | Encrypted PIN data | PIN block, KSN | ❌ |
| **Emv** | EMV chip data | Tags, cryptogram, AID | ❌ |
| **Terminal** | Terminal details | ID, capabilities, location | ❌ |
| **Signature** | Customer signature | Signature image/data | ❌ |

### Priority 5: Shopper & Order DTOs (MEDIUM)

| DTO | Purpose | Properties | Status |
|-----|---------|-----------|--------|
| **Shopper** | Shopper profile | Name, email, stored payments | ❌ |
| **Order** | Order details | Items, amounts, references | ❌ |
| **OrderItem** | Individual order item | Description, amount, quantity, type | ❌ |
| **DebtorAccount** | Debtor account (MCC 6012/6050/6051) | Account details for specific MCCs | ❌ |

### Priority 6: Payment Session DTOs (MEDIUM)

| DTO | Purpose | Properties | Status |
|-----|---------|-----------|--------|
| **PaymentSession** | Payment session | Expiry, allowed methods, return URL | ❌ |
| **PaymentLink** | Payment link | URL, status, amount, expiry | ❌ |
| **PaymentMethodLink** | Payment method link | Similar to payment link | ❌ |
| **PaymentMethodSession** | Payment method session | Similar to payment session | ❌ |
| **PaymentLinkEvent** | Payment link event | Event type, timestamp, details | ❌ |
| **ApplePayPaymentSession** | Apple Pay session | Merchant session data | ❌ |

### Priority 7: Subscription DTOs (MEDIUM)

| DTO | Purpose | Properties | Status |
|-----|---------|-----------|--------|
| **Plan** | Subscription plan | Amount, interval, trial period | ❌ |
| **Subscription** | Active subscription | Plan, shopper, status, next billing | ❌ |
| **SubscriptionSurcharge** | Subscription surcharge | Surcharge for subscriptions | ❌ |

### Priority 8: Batch DTOs (MEDIUM)

| DTO | Purpose | Properties | Status |
|-----|---------|-----------|--------|
| **Batch** | Settlement batch | Date, count, totals, state | ❌ |
| **ManualBatch** | Manual batch | Similar to Batch + manual control | ❌ |
| **TotalAdjustment** | Total adjustment | Adjustment amount, reason | ❌ |

### Priority 9: Account & Config DTOs (LOW)

| DTO | Purpose | Properties | Status |
|-----|---------|-----------|--------|
| **Merchant** | Merchant details | Name, ID, regions, processors | ❌ |
| **Account** | Account details | Merchant, settings, logo | ❌ |
| **ProcessorAccount** | Processor account | Processor, capabilities, field policies | ❌ |
| **ProcessorAccountFieldPolicies** | Field validation policies | Policies for various fields | ❌ |
| **DebitSteering** | Debit steering config | Preference, schemes | ❌ |
| **PinlessDebit** | Pinless debit config | Card schemes, settings | ❌ |
| **Surcharging** | Surcharge config | Enabled methods, rules | ❌ |

### Priority 10: Other DTOs (LOW)

| DTO | Purpose | Properties | Status |
|-----|---------|-----------|--------|
| **Notification** | Webhook notification | Event type, resource, data | ❌ |
| **EmailReceiptRequest** | Email receipt | Email address, language | ❌ |
| **PanToken** | PAN tokenization | Tokens, card references | ❌ |
| **EmvKeys** | EMV encryption keys | Key data for terminals | ❌ |
| **EmvKey** | Single EMV key | Key index, data, algorithm | ❌ |
| **ProvisioningCode** | Terminal provisioning | Activation code | ❌ |
| **HistoryEntry** | Resource change history | Timestamp, changes, user | ❌ |

### Value Objects

| Value Object | Purpose | Properties | Status |
|--------------|---------|-----------|--------|
| **Money** | Amount + currency | amount (string), currencyCode (Currency enum) | ✅ Complete |
| **Address** | Physical address | street1, street2, city, region, postal code, country | ❌ (Use Contact instead?) |

**Note:** The `Contact` DTO already includes address fields, so a separate `Address` VO may not be needed unless we want to reuse it in multiple places.

---

## Enums

### Priority 1: Payment Enums (Complete ✅)

| Enum | Values | Status |
|------|--------|--------|
| **TransactionState** | declined, authorized, authorizationPending, captured, settled, reversed | ✅ |
| **TransactionType** | sale, refund, void | ✅ |
| **PaymentMethod** | Card, BLIK, ACH | ✅ |
| **PaymentMethodOrigin** | Card, Apple Pay, Google Pay, Paze, BLIK, ACH, Polish Bank Transfer, Unknown Wallet | ✅ |
| **PaymentMethodQualifier** | credit, debit | ✅ |
| **ProcessorDirective** | none, reversal | ✅ |
| **Source** | 12 values including directApiCall, virtualTerminal, etc. | ✅ |
| **MarketSegment** | retail, restaurant | ✅ |
| **ShopperInteraction** | ecommerce, mailOrder, telephoneOrder, merchantInitiated, inPerson | ✅ |
| **MarkupRateAnnotation** | none, aboveEcb, belowEcb | ✅ |
| **Currency** | ISO 4217 codes (USD, EUR, GBP, etc.) | ✅ |
| **CardScheme** | Visa, Mastercard, Amex, Discover, Diners, JCB, Maestro, etc. | ✅ |

### Priority 2: New Enums Needed (HIGH)

| Enum | Values | OpenAPI Path |
|------|--------|--------------|
| **CredentialOnFileType** | initial, subscription, unscheduledCardholderInitiated, unscheduledMerchantInitiated | schemas/CredentialOnFileType |
| **AchAccountType** | savingsPersonal, checkingPersonal, checkingBusiness | schemas/AchAccountType |
| **EventType** | transactionAuthorized, transactionDeclined, subscriptionCreated, etc. | schemas/EventType |
| **ResourceType** | transaction, shopper, subscription, paymentLink, etc. | schemas/ResourceType |
| **BatchState** | open, closed, settled | schemas/BatchState |
| **SubscriptionState** | active, paused, cancelled, expired | schemas/SubscriptionState |
| **PaymentLinkStatus** | active, expired, consumed | schemas/PaymentLinkStatus |
| **TransactionAdvice** | recurringTransaction, installmentTransaction, standingOrder, etc. | schemas/TransactionAdvice |

### Priority 3: Terminal & Device Enums (MEDIUM)

| Enum | Values | OpenAPI Path |
|------|--------|--------------|
| **TerminalType** | pos, mpos, ecommerce, unattended, etc. | schemas/TerminalType |
| **DeviceFormat** | Various encryption formats | schemas/DeviceFormat |
| **CardholderVerificationMethod** | pin, signature, noCvm, etc. | schemas/CardholderVerificationMethod |
| **AccountEntryMode** | manual, swipe, chip, contactless, etc. | schemas/AccountEntryMode |
| **SignatureVerification** | verified, notVerified, notRequired | schemas/SignatureVerification |

### Priority 4: Order & Item Enums (MEDIUM)

| Enum | Values | OpenAPI Path |
|------|--------|--------------|
| **OrderItemType** | product, shipping, discount, tax | schemas/OrderItemType |

### Priority 5: Subscription Enums (MEDIUM)

| Enum | Values | OpenAPI Path |
|------|--------|--------------|
| **BillingInterval** | day, week, month, year | schemas/BillingInterval |
| **TimeUnit** | day, week, month, year | schemas/TimeUnit |

### Priority 6: Other Enums (LOW)

| Enum | Values | OpenAPI Path |
|------|--------|--------------|
| **Region** | us, emea, etc. | schemas/Region |
| **HppType** | standard, iframe | schemas/HppType |
| **PaymentLinkEventType** | created, viewed, transactionAttempted, etc. | schemas/PaymentLinkEventType |
| **DebitSteeringPreference** | none, global, usCommonDebit | schemas/DebitSteeringPreference |
| **PinlessDebitCardScheme** | visa, mastercard, discover | schemas/PinlessDebitCardScheme |
| **FieldPolicy** | required, optional, forbidden, readonly | schemas/FieldPolicy |
| **SalesTaxDefaultEntryMethod** | merchant, shopper | schemas/SalesTaxDefaultEntryMethod |
| **TrueFalseOrDefault** | true, false, default | schemas/TrueFalseOrDefault |
| **TrueFalseOrUnknown** | true, false, unknown | schemas/TrueFalseOrUnknown |
| **CardFundingSource** | credit, debit, prepaid, unknown | schemas/CardFundingSource |

---

## Validation Strategy

### Current Validation Approach

**Location:** DTOs validate their own data in private `validate()` methods called from `__construct()`

**Examples:**
- `Money`: Validates currency code format, positive amounts
- `Contact`: Validates country code length (3 chars), email max length (254)
- `ShopperStatement`: Validates max lengths (name: 25, phone: 20, url: 13)
- `ThreeDSecure`: Validates UUID format, status codes, version format, ECI values, auth value length
- `Card`: Validates pan format, CVV format, expiry dates

**Pattern:**
```php
public function __construct(
    public readonly string $property,
    // ...
) {
    $this->validate();
}

private function validate(): void
{
    if (!$this->meetsCondition()) {
        throw new InvalidArgumentException('Validation message');
    }
}
```

### Proposed Validation Architecture

We should maintain the current pattern but add **optional validation helpers** for complex cross-field or business rule validations.

#### 1. **DTO Self-Validation** (Current - Keep)

Each DTO validates its own structural integrity:
- Data type correctness
- Format validation (UUIDs, emails, phone numbers)
- Length constraints
- Pattern matching (regex)
- Enum value validation
- Required field presence

**Advantages:**
- ✅ Fail-fast - errors caught immediately on construction
- ✅ Type safety - invalid DTOs cannot exist
- ✅ Clear error messages at construction time
- ✅ No need to remember to validate

**Location:** `private validate(): void` method in each DTO

#### 2. **Message-Level Validation** (Current - Keep)

Request messages validate **required fields for that specific operation**:
- Different operations have different required fields
- Example: Create transaction requires `total` and `card`, but update doesn't

**Location:** Request message classes (e.g., `CreateTransactionRequest::validateTransactionRequest()`)

#### 3. **Optional Validator Helpers** (NEW - Proposed)

For complex business rules and cross-DTO validation:

```php
namespace Academe\Elavon\Epg\Psr7\Validation;

class TransactionValidator
{
    /**
     * Validates complex business rules for transactions.
     *
     * @param Transaction $transaction
     * @return ValidationResult
     */
    public function validate(Transaction $transaction): ValidationResult
    {
        $errors = [];

        // Business rule: Refunds require parent transaction
        if ($transaction->type === TransactionType::REFUND) {
            if ($transaction->parentTransaction === null) {
                $errors[] = new ValidationError(
                    code: 'refund.missingParent',
                    message: 'Refund transactions must reference a parent transaction',
                    field: 'parentTransaction'
                );
            }
        }

        // Business rule: Card transactions require card data
        if ($transaction->paymentMethod === PaymentMethod::CARD) {
            if ($transaction->card === null && $transaction->hostedCard === null
                && $transaction->storedCard === null) {
                $errors[] = new ValidationError(
                    code: 'card.missingCardData',
                    message: 'Card transactions require card, hostedCard, or storedCard',
                    field: 'card'
                );
            }
        }

        // Business rule: 3DS transactions must include 3DS data
        if ($transaction->shopperInteraction === ShopperInteraction::ECOMMERCE) {
            if ($transaction->total !== null && $transaction->total->amount > 30.00) {
                if ($transaction->threeDSecure === null) {
                    $errors[] = new ValidationError(
                        code: '3ds.requiredForAmount',
                        message: 'Transactions over 30.00 require 3D Secure authentication',
                        field: 'threeDSecure'
                    );
                }
            }
        }

        return new ValidationResult($errors);
    }
}

class ValidationResult
{
    /** @param array<ValidationError> $errors */
    public function __construct(
        private readonly array $errors = []
    ) {}

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getErrorMessages(): array
    {
        return array_map(fn($e) => $e->message, $this->errors);
    }
}

class ValidationError
{
    public function __construct(
        public readonly string $code,
        public readonly string $message,
        public readonly ?string $field = null,
    ) {}
}
```

**Usage:**
```php
$transaction = Transaction::fromArray($data); // DTO validation happens here

// Optional business rule validation
$validator = new TransactionValidator();
$result = $validator->validate($transaction);

if (!$result->isValid()) {
    foreach ($result->getErrors() as $error) {
        echo "{$error->field}: {$error->message} (Code: {$error->code})\n";
    }
}
```

**When to Use Validators:**
- Complex business rules
- Cross-field dependencies
- Conditional requirements based on payment method
- Rules that might change based on merchant configuration
- Warnings (non-fatal validations)

**When NOT to Use Validators:**
- Simple type/format validation → Use DTO `validate()`
- Required field checking → Use Request message validation

#### 4. **Validation Categories**

| Category | Location | Example |
|----------|----------|---------|
| **Structural** | DTO `validate()` | UUID format, email format, max length |
| **Required Fields** | Request `validate{Resource}Request()` | Transaction must have total for Create |
| **Business Rules** | Optional Validator classes | Refunds need parent transaction |
| **Payment Method** | Optional Validator classes | ACH requires routing number |
| **Merchant Config** | Optional Validator classes | 3DS required for certain merchants |

---

## Implementation Phases

### Phase 1: Core Transaction Messages (WEEK 1)

**Goal:** Complete the transaction message set

**Tasks:**
1. ✅ `CreateTransactionRequest` - Already exists
2. ✅ `TransactionResponse` - Already exists
3. ❌ `UpdateTransactionRequest` - PATCH /transactions/{id}
4. ❌ `RetrieveTransactionRequest` - GET /transactions/{id}
5. ❌ `TransactionListResponse` - GET /transactions (paginated)

**New DTOs Needed:**
- None (Transaction DTO is complete)

**Testing:**
- Integration tests with mock PSR-7 responses
- Round-trip serialization tests

### Phase 2: Essential Payment DTOs (WEEK 1-2)

**Goal:** Add missing payment method DTOs

**Tasks:**
1. ❌ Create `AchPayment` DTO
2. ❌ Create `HostedCard` DTO (similar to Card)
3. ❌ Create `StoredCard` DTO
4. ❌ Create `StoredAchPayment` DTO
5. ❌ Create `HostedAchPayment` DTO
6. ❌ Create `HsmCard` DTO
7. ❌ Create `Wallet` DTO
8. ❌ Create `Blik` DTO
9. ❌ Update `Transaction` to include these DTOs

**Testing:**
- Unit tests for each DTO (fromArray, toArray, validation)
- Test integration with Transaction DTO

### Phase 3: Wallet Payment Messages (WEEK 2)

**Goal:** Support Apple Pay, Google Pay, Paze

**Tasks:**
1. ❌ Create `ApplePayPayment` DTO
2. ❌ Create `GooglePayPayment` DTO
3. ❌ Create `PazePayment` DTO
4. ❌ Create `CreateApplePayPaymentRequest`
5. ❌ Create `ApplePayPaymentResponse`
6. ❌ Create `CreateGooglePayPaymentRequest`
7. ❌ Create `GooglePayPaymentResponse`
8. ❌ Create `CreatePazePaymentRequest`
9. ❌ Create `PazePaymentResponse`

**New Enums:**
- None (PaymentMethodOrigin already has these)

**Testing:**
- Test wallet-specific validation rules
- Test encrypted payment data handling

### Phase 4: Forex & Surcharge (WEEK 2-3)

**Goal:** Support currency conversion and surcharge calculation

**Tasks:**
1. ❌ Create `ForexAdvice` DTO
2. ❌ Create `SurchargeAdvice` DTO
3. ❌ Create `RefundSurchargeAdvice` DTO
4. ❌ Create `CreateForexAdviceRequest`
5. ❌ Create `ForexAdviceResponse`
6. ❌ Create `CreateSurchargeAdviceRequest`
7. ❌ Create `SurchargeAdviceResponse`
8. ❌ Create `CreateRefundSurchargeAdviceRequest`
9. ❌ Create `RefundSurchargeAdviceResponse`

**Testing:**
- Test rate calculations
- Test expiry handling

### Phase 5: Hosted Cards & Storage (WEEK 3)

**Goal:** Support hosted cards and stored payment methods

**Tasks:**
1. ❌ Create `CreateHostedCardRequest`
2. ❌ Create `HostedCardResponse`
3. ❌ Create `UpdateHostedCardRequest`
4. ❌ Create `CreateStoredCardRequest`
5. ❌ Create `StoredCardResponse`
6. ❌ Create `UpdateStoredCardRequest`
7. ❌ Create `DeleteStoredCardRequest`
8. ❌ Create `CreateHostedAchPaymentRequest`
9. ❌ Create `HostedAchPaymentResponse`
10. ❌ Create `CreateStoredAchPaymentRequest`
11. ❌ Create `StoredAchPaymentResponse`
12. ❌ Create `UpdateStoredAchPaymentRequest`
13. ❌ Create `DeleteStoredAchPaymentRequest`

**New Enums:**
- ❌ `CredentialOnFileType`

**Testing:**
- Test PCI-compliant data handling
- Test tokenization workflows

### Phase 6: Shopper Management (WEEK 3-4)

**Goal:** Support shopper profiles

**Tasks:**
1. ❌ Create `Shopper` DTO
2. ❌ Create `CreateShopperRequest`
3. ❌ Create `ShopperResponse`
4. ❌ Create `UpdateShopperRequest`
5. ❌ Create `DeleteShopperRequest`
6. ❌ Create `ShopperListResponse`

**Testing:**
- Test shopper with multiple stored cards
- Test shopper custom fields

### Phase 7: Payment Sessions & Links (WEEK 4-5)

**Goal:** Support hosted payment pages

**Tasks:**
1. ❌ Create `PaymentSession` DTO
2. ❌ Create `PaymentLink` DTO
3. ❌ Create `PaymentMethodLink` DTO
4. ❌ Create `PaymentMethodSession` DTO
5. ❌ Create `PaymentLinkEvent` DTO
6. ❌ Create `ApplePayPaymentSession` DTO
7. ❌ Create request/response messages for each

**New Enums:**
- ❌ `PaymentLinkStatus`
- ❌ `PaymentLinkEventType`
- ❌ `HppType`

**Testing:**
- Test expiry handling
- Test return URL validation

### Phase 8: Subscriptions & Plans (WEEK 5-6)

**Goal:** Support recurring billing

**Tasks:**
1. ❌ Create `Plan` DTO
2. ❌ Create `Subscription` DTO
3. ❌ Create `SubscriptionSurcharge` DTO
4. ❌ Create request/response messages

**New Enums:**
- ❌ `SubscriptionState`
- ❌ `BillingInterval`
- ❌ `TimeUnit`

**Testing:**
- Test billing cycle calculations
- Test subscription state transitions

### Phase 9: Orders (WEEK 6)

**Goal:** Support order management

**Tasks:**
1. ❌ Create `Order` DTO
2. ❌ Create `OrderItem` DTO
3. ❌ Create request/response messages

**New Enums:**
- ❌ `OrderItemType`

**Testing:**
- Test order total calculations
- Test line item management

### Phase 10: Batch Processing (WEEK 6-7)

**Goal:** Support batch settlement

**Tasks:**
1. ❌ Create `Batch` DTO
2. ❌ Create `ManualBatch` DTO
3. ❌ Create `TotalAdjustment` DTO
4. ❌ Create request/response messages

**New Enums:**
- ❌ `BatchState`

**Testing:**
- Test batch state transitions
- Test total calculations

### Phase 11: Terminal & Device (WEEK 7-8)

**Goal:** Support POS terminals

**Tasks:**
1. ❌ Create `Terminal` DTO
2. ❌ Create `PointOfInteraction` DTO
3. ❌ Create `DeviceInteraction` DTO
4. ❌ Create `EncryptedData` DTO
5. ❌ Create `PinEncryptedData` DTO
6. ❌ Create `Emv` DTO
7. ❌ Create `EmvKey` DTO
8. ❌ Create `EmvKeys` DTO
9. ❌ Create `Signature` DTO
10. ❌ Create `ProvisioningCode` DTO
11. ❌ Create `HsmCard` DTO
12. ❌ Create request/response messages

**New Enums:**
- ❌ `TerminalType`
- ❌ `DeviceFormat`
- ❌ `CardholderVerificationMethod`
- ❌ `AccountEntryMode`
- ❌ `SignatureVerification`

**Testing:**
- Test EMV data handling
- Test encryption key management

### Phase 12: Account & Configuration (WEEK 8)

**Goal:** Read-only configuration access

**Tasks:**
1. ❌ Create `Merchant` DTO
2. ❌ Create `Account` DTO
3. ❌ Create `ProcessorAccount` DTO
4. ❌ Create `ProcessorAccountFieldPolicies` DTO
5. ❌ Create `DebitSteering` DTO
6. ❌ Create `PinlessDebit` DTO
7. ❌ Create `Surcharging` DTO
8. ❌ Create request/response messages

**New Enums:**
- ❌ `Region`
- ❌ `DebitSteeringPreference`
- ❌ `PinlessDebitCardScheme`
- ❌ `FieldPolicy`

**Testing:**
- Test configuration retrieval
- Test field policy enforcement

### Phase 13: Verification & Security (WEEK 9)

**Goal:** Card verification and security

**Tasks:**
1. ❌ Create `VerificationResults` DTO
2. ❌ Create `Verification` DTO
3. ❌ Create `PartialAuthorization` DTO
4. ❌ Update Transaction with these fields

**Testing:**
- Test AVS/CVV result parsing
- Test partial authorization handling

### Phase 14: Notifications & Events (WEEK 9)

**Goal:** Webhook handling

**Tasks:**
1. ❌ Create `Notification` DTO
2. ❌ Create `NotificationResponse`
3. ❌ Create `NotificationListResponse`

**New Enums:**
- ❌ `EventType`
- ❌ `ResourceType`

**Testing:**
- Test webhook payload parsing
- Test event type routing

### Phase 15: Specialized Operations (WEEK 10)

**Goal:** Less common operations

**Tasks:**
1. ❌ Create `EmailReceiptRequest` DTO
2. ❌ Create `CreateEmailReceiptRequest`
3. ❌ Create `EmailReceiptResponse`
4. ❌ Create `PanToken` DTO
5. ❌ Create `CreatePanTokensRequest`
6. ❌ Create `PanTokensResponse`

**Testing:**
- Test email receipt generation
- Test PAN tokenization

### Phase 16: Validation Helpers (WEEK 10)

**Goal:** Optional business rule validators

**Tasks:**
1. ❌ Create `ValidationResult` class
2. ❌ Create `ValidationError` class
3. ❌ Create `TransactionValidator`
4. ❌ Create `PaymentMethodValidator`
5. ❌ Create `SubscriptionValidator`
6. ❌ Create `OrderValidator`
7. ❌ Document validation patterns

**Testing:**
- Test each validation rule
- Test error message clarity

---

## Priority Classification

### Must-Have (Immediate - Weeks 1-3)

Essential for basic payment processing:

1. **Transaction Messages** (Phase 1)
2. **Payment Method DTOs** (Phase 2)
3. **Wallet Payments** (Phase 3)
4. **Forex & Surcharge** (Phase 4)
5. **Hosted Cards** (Phase 5)

### Should-Have (High Priority - Weeks 3-6)

Important for full functionality:

6. **Shoppers** (Phase 6)
7. **Payment Sessions & Links** (Phase 7)
8. **Subscriptions** (Phase 8)
9. **Orders** (Phase 9)

### Nice-to-Have (Medium Priority - Weeks 6-9)

Useful but not immediately critical:

10. **Batches** (Phase 10)
11. **Terminals & Devices** (Phase 11)
12. **Account & Config** (Phase 12)
13. **Verification** (Phase 13)

### Optional (Low Priority - Weeks 9-10)

Can be deferred:

14. **Notifications** (Phase 14)
15. **Specialized Ops** (Phase 15)
16. **Validation Helpers** (Phase 16)

---

## Summary Statistics

### Total Message Count

| Message Type | Count |
|--------------|-------|
| Create Requests | ~30 |
| Update Requests | ~15 |
| Delete Requests | ~4 |
| Single Response | ~30 |
| List Responses | ~20 |
| **Total Messages** | **~99** |

### Total DTO Count

| DTO Category | Count |
|--------------|-------|
| Payment DTOs | ~15 |
| Transaction Support | ~10 |
| Terminal & Device | ~10 |
| Shopper & Order | ~5 |
| Payment Session | ~6 |
| Subscription | ~3 |
| Batch | ~3 |
| Account & Config | ~8 |
| Other | ~10 |
| **Total DTOs** | **~70** |

### Total Enum Count

| Enum Category | Count |
|--------------|-------|
| Existing | 12 ✅ |
| Payment Enums | ~8 |
| Terminal Enums | ~5 |
| Other Enums | ~15 |
| **Total Enums** | **~40** |

### Implementation Effort

| Priority | Phases | Estimated Weeks | Messages | DTOs | Enums |
|----------|--------|----------------|----------|------|-------|
| Must-Have | 1-5 | 3 weeks | ~30 | ~25 | ~3 |
| Should-Have | 6-9 | 3 weeks | ~30 | ~15 | ~10 |
| Nice-to-Have | 10-13 | 3 weeks | ~25 | ~20 | ~15 |
| Optional | 14-16 | 2 weeks | ~14 | ~10 | ~12 |
| **Total** | **16 phases** | **~11 weeks** | **~99** | **~70** | **~40** |

---

## Next Steps

1. **Review & Approve Plan** - Stakeholder review of priorities and scope
2. **Begin Phase 1** - Complete core transaction messages
3. **Set Up CI/CD** - Automated testing for each new message
4. **Documentation** - Update README with usage examples
5. **OpenAPI Sync** - Ensure alignment with latest OpenAPI spec

---

## Notes

- All message classes follow consistent PSR-7 patterns
- All DTOs include `fromArray()`, `toArray()`, and `validate()` methods
- Error handling is standardized via `HandlesErrors` trait
- Request messages validate required fields per operation
- Response messages parse both success and error responses
- List responses handle pagination with next/first links
- Validation is layered: DTO structural → Request required → Optional business rules
- Testing is comprehensive: unit tests for DTOs, integration tests for messages
- All dates/times are ISO 8601 format strings (not DateTimeImmutable for simplicity)
- Money amounts are strings (decimal values) with Currency enum
- Resource URLs are strings (not parsed URIs for flexibility)
- This implementation supports OpenAPI spec version 2025-10-01
