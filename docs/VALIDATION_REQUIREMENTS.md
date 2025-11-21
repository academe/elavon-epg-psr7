# Validation Requirements & Strategy

This document details the validation requirements for the Elavon EPG PSR-7 API package and explains where different types of validation should be implemented.

## Validation Layers

### Layer 1: DTO Structural Validation (MANDATORY)

**Where:** Private `validate()` method in each DTO, called from `__construct()`

**When:** Immediately upon DTO construction

**Purpose:** Ensure DTOs have valid internal structure and cannot exist in an invalid state

**Validates:**
- Data type correctness
- Format validation (UUIDs, emails, phone numbers, etc.)
- Length constraints (min/max)
- Pattern matching (regex)
- Enum value validation
- Required property presence (for DTO construction)
- Numeric ranges

**Examples:**

```php
// Money DTO - validates positive amount
private function validate(): void
{
    if (!$this->isPositive()) {
        throw new InvalidArgumentException('Amount must be positive');
    }
}

// Contact DTO - validates country code and email
private function validate(): void
{
    if ($this->countryCode !== null && strlen($this->countryCode) !== 3) {
        throw new InvalidArgumentException('Country code must be exactly 3 characters (ISO 3166-1 alpha-3)');
    }

    if ($this->email !== null && strlen($this->email) > 254) {
        throw new InvalidArgumentException('Email address must not exceed 254 characters');
    }
}

// ThreeDSecure DTO - validates UUID format, status codes, version
private function validate(): void
{
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[12345][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
        $this->directoryServerTransactionId)) {
        throw new InvalidArgumentException('Directory server transaction ID must be a valid UUID');
    }

    if (!preg_match('/^[YNUA]$/', $this->transactionStatus)) {
        throw new InvalidArgumentException('Transaction status must be Y, N, U, or A');
    }
}
```

**Decision:** Always throw `InvalidArgumentException` for validation failures

---

### Layer 2: Request Message Validation (MANDATORY)

**Where:** Request message classes (e.g., `CreateTransactionRequest`)

**When:** Before building the PSR-7 request

**Purpose:** Ensure required fields are present for the specific API operation

**Validates:**
- Required fields for CREATE operations
- Required fields for UPDATE operations (usually fewer than CREATE)
- Operation-specific constraints

**Examples:**

```php
class CreateTransactionRequest
{
    private const DEFAULT_REQUIRED_FIELDS = [
        'total',
        'card',  // Required for card transactions
    ];

    private function validateTransactionRequest(Transaction $transaction): void
    {
        $fieldsToCheck = $this->requiredFields ?? self::DEFAULT_REQUIRED_FIELDS;

        foreach ($fieldsToCheck as $field) {
            if (!property_exists($transaction, $field)) {
                throw new InvalidArgumentException("Unknown required field: {$field}");
            }

            if ($transaction->$field === null) {
                throw new InvalidArgumentException("Transaction {$field} is required for creating a transaction");
            }
        }
    }
}
```

**Note:** Different operations have different requirements:
- `CreateTransactionRequest` requires `total` and `card`
- `UpdateTransactionRequest` has NO required fields (partial updates)
- `CreateRefundRequest` requires `parentTransaction` and `total`

**Decision:** Required fields are configurable per request type

---

### Layer 3: Optional Business Rule Validation (OPTIONAL)

**Where:** Validator classes in `src/Validation/` namespace

**When:** Explicitly called by application code

**Purpose:** Validate complex business rules, cross-field dependencies, and conditional logic

**Validates:**
- Payment method specific rules
- Cross-field dependencies
- Conditional requirements
- Business logic constraints
- Merchant-specific configurations
- Warning-level validations (non-fatal)

**Examples:**

```php
class TransactionValidator
{
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
            if ($transaction->card === null
                && $transaction->hostedCard === null
                && $transaction->storedCard === null) {
                $errors[] = new ValidationError(
                    code: 'card.missingCardData',
                    message: 'Card transactions require card, hostedCard, or storedCard',
                    field: 'card'
                );
            }
        }

        // Business rule: ACH transactions require ACH data
        if ($transaction->paymentMethod === PaymentMethod::ACH) {
            if ($transaction->achPayment === null
                && $transaction->hostedAchPayment === null
                && $transaction->storedAchPayment === null) {
                $errors[] = new ValidationError(
                    code: 'ach.missingAchData',
                    message: 'ACH transactions require achPayment, hostedAchPayment, or storedAchPayment',
                    field: 'achPayment'
                );
            }
        }

        return new ValidationResult($errors);
    }
}
```

**Usage:**
```php
// DTOs are automatically validated on construction
$transaction = Transaction::fromArray($data); // Throws if structurally invalid

// Request validation happens in build()
$request = new CreateTransactionRequest($transaction);
$psrRequest = $request->build(); // Throws if required fields missing

// Optional business rule validation
$validator = new TransactionValidator();
$result = $validator->validate($transaction);

if (!$result->isValid()) {
    // Handle business rule violations
    foreach ($result->getErrors() as $error) {
        echo "{$error->field}: {$error->message}\n";
    }
}
```

**Decision:** Validators are OPTIONAL - applications choose which rules to enforce

---

## Validation Requirements by DTO

### Transaction DTO

| Field | Validation | Layer | Rule |
|-------|-----------|-------|------|
| total | Must be positive Money | 1 (DTO) | Amount > 0 |
| type | Must be valid TransactionType | 1 (DTO) | Enum validation |
| paymentMethod | Must be valid PaymentMethod | 1 (DTO) | Enum validation |
| shopperEmailAddress | Valid email format | 1 (DTO) | Max 254 chars |
| parentTransaction | Required for refunds | 3 (Business) | Conditional requirement |
| card/hostedCard/storedCard | At least one required for card payments | 3 (Business) | Payment method rule |

### Card DTO

| Field | Validation | Layer | Rule |
|-------|-----------|-------|------|
| pan | Valid card number format | 1 (DTO) | Luhn check, 13-19 digits |
| cvv | 3-4 digits | 1 (DTO) | Regex: `^\d{3,4}$` |
| expirationMonth | 01-12 | 1 (DTO) | Range validation |
| expirationYear | 4 digits, not expired | 1 (DTO) | Format + expiry check |
| holderName | Not empty | 1 (DTO) | Min length 1 |

### Money DTO

| Field | Validation | Layer | Rule |
|-------|-----------|-------|------|
| amount | Positive decimal | 1 (DTO) | bccomp($amount, '0') > 0 |
| currencyCode | Valid ISO 4217 code | 1 (DTO) | Enum validation |

### Contact DTO

| Field | Validation | Layer | Rule |
|-------|-----------|-------|------|
| countryCode | ISO 3166-1 alpha-3 | 1 (DTO) | Exactly 3 characters |
| email | Valid email | 1 (DTO) | Max 254 characters |
| postalCode | Format varies by country | 3 (Business) | Country-specific rules |

### ShopperStatement DTO

| Field | Validation | Layer | Rule |
|-------|-----------|-------|------|
| name | Max length | 1 (DTO) | 25 characters max |
| phone | Max length | 1 (DTO) | 20 characters max |
| url | Max length | 1 (DTO) | 13 characters max |

### ThreeDSecure DTO

| Field | Validation | Layer | Rule |
|-------|-----------|-------|------|
| directoryServerTransactionId | UUID format | 1 (DTO) | RFC 4122 UUID pattern |
| transactionStatus | Y/N/U/A | 1 (DTO) | Enum: [YNUA] |
| protocolVersion | Version format | 1 (DTO) | Pattern: `\d+.\d+.\d+` |
| electronicCommerceIndicator | Valid ECI | 1 (DTO) | 0/1/2/5/6/7 with optional leading 0 |
| authenticationValue | Base64, 28 chars | 1 (DTO) | Exactly 28 characters |

### AchPayment DTO (Planned)

| Field | Validation | Layer | Rule |
|-------|-----------|-------|------|
| routingNumber | 9 digits | 1 (DTO) | ABA routing number format |
| accountNumber | Not empty | 1 (DTO) | Min length 1, max varies |
| accountType | Valid type | 1 (DTO) | Enum: savingsPersonal, checkingPersonal, checkingBusiness |

### HostedCard DTO (Planned)

| Field | Validation | Layer | Rule |
|-------|-----------|-------|------|
| id | Not empty | 1 (DTO) | Resource ID format |
| expirationMonth | 01-12 | 1 (DTO) | Range validation |
| expirationYear | 4 digits | 1 (DTO) | Format validation |

### ForexAdvice DTO (Planned)

| Field | Validation | Layer | Rule |
|-------|-----------|-------|------|
| conversionRate | Positive decimal | 1 (DTO) | Must be > 0 |
| expiresAt | ISO 8601 datetime | 1 (DTO) | Format validation |
| sourceAmount | Money | 1 (DTO) | Positive |
| targetAmount | Money | 1 (DTO) | Positive |

### Shopper DTO (Planned)

| Field | Validation | Layer | Rule |
|-------|-----------|-------|------|
| email | Valid email | 1 (DTO) | Email format, max 254 chars |
| customReference | Max length | 1 (DTO) | 255 characters max |

### Order DTO (Planned)

| Field | Validation | Layer | Rule |
|-------|-----------|-------|------|
| items | At least one item | 3 (Business) | Array not empty |
| total | Sum of items | 3 (Business) | Calculated total matches |

### OrderItem DTO (Planned)

| Field | Validation | Layer | Rule |
|-------|-----------|-------|------|
| amount | Money | 1 (DTO) | Can be positive or negative (discounts) |
| quantity | Positive integer | 1 (DTO) | >= 1 |
| type | Valid type | 1 (DTO) | Enum: product, shipping, discount, tax |

### Plan DTO (Planned)

| Field | Validation | Layer | Rule |
|-------|-----------|-------|------|
| amount | Positive Money | 1 (DTO) | Amount > 0 |
| billingInterval | Valid interval | 1 (DTO) | Enum: day, week, month, year |
| billingIntervalCount | Positive integer | 1 (DTO) | >= 1 |

### Subscription DTO (Planned)

| Field | Validation | Layer | Rule |
|-------|-----------|-------|------|
| plan | Not null | 2 (Request) | Required for creation |
| shopper | Not null | 2 (Request) | Required for creation |
| storedCard/storedAchPayment | At least one | 3 (Business) | Payment method required |

---

## Complex Validation Rules (Business Layer)

### Rule 1: Payment Method Validation

```php
class PaymentMethodValidator
{
    public function validate(Transaction $transaction): ValidationResult
    {
        $errors = [];

        if ($transaction->paymentMethod === null) {
            return new ValidationResult($errors); // No validation if no payment method set
        }

        switch ($transaction->paymentMethod) {
            case PaymentMethod::CARD:
                if (!$this->hasCardData($transaction)) {
                    $errors[] = new ValidationError(
                        'payment.cardDataRequired',
                        'Card payment method requires card, hostedCard, storedCard, or hsmCard',
                        'card'
                    );
                }
                break;

            case PaymentMethod::ACH:
                if (!$this->hasAchData($transaction)) {
                    $errors[] = new ValidationError(
                        'payment.achDataRequired',
                        'ACH payment method requires achPayment, hostedAchPayment, or storedAchPayment',
                        'achPayment'
                    );
                }
                break;

            case PaymentMethod::BLIK:
                if ($transaction->blik === null) {
                    $errors[] = new ValidationError(
                        'payment.blikDataRequired',
                        'BLIK payment method requires blik data',
                        'blik'
                    );
                }
                break;
        }

        return new ValidationResult($errors);
    }

    private function hasCardData(Transaction $transaction): bool
    {
        return $transaction->card !== null
            || $transaction->hostedCard !== null
            || $transaction->storedCard !== null
            || $transaction->hsmCard !== null
            || $transaction->applePayPayment !== null
            || $transaction->googlePayPayment !== null
            || $transaction->pazePayment !== null;
    }

    private function hasAchData(Transaction $transaction): bool
    {
        return $transaction->achPayment !== null
            || $transaction->hostedAchPayment !== null
            || $transaction->storedAchPayment !== null;
    }
}
```

### Rule 2: Transaction Type Validation

```php
class TransactionTypeValidator
{
    public function validate(Transaction $transaction): ValidationResult
    {
        $errors = [];

        if ($transaction->type === null) {
            return new ValidationResult($errors);
        }

        switch ($transaction->type) {
            case TransactionType::REFUND:
                // Refunds must reference a parent transaction
                if ($transaction->parentTransaction === null) {
                    $errors[] = new ValidationError(
                        'refund.parentRequired',
                        'Refund transactions must include parentTransaction',
                        'parentTransaction'
                    );
                }
                // Refund amount cannot exceed original transaction
                // (This would require fetching parent transaction - merchant responsibility)
                break;

            case TransactionType::VOID:
                // Voids must reference a transaction to void
                if ($transaction->parentTransaction === null) {
                    $errors[] = new ValidationError(
                        'void.parentRequired',
                        'Void transactions must include parentTransaction',
                        'parentTransaction'
                    );
                }
                // Voids cannot have an amount
                if ($transaction->total !== null) {
                    $errors[] = new ValidationError(
                        'void.amountNotAllowed',
                        'Void transactions should not include a total amount',
                        'total'
                    );
                }
                break;

            case TransactionType::SALE:
                // Sales must have an amount
                if ($transaction->total === null) {
                    $errors[] = new ValidationError(
                        'sale.totalRequired',
                        'Sale transactions must include a total amount',
                        'total'
                    );
                }
                break;
        }

        return new ValidationResult($errors);
    }
}
```

### Rule 3: 3D Secure Validation

```php
class ThreeDSecureValidator
{
    public function validate(Transaction $transaction): ValidationResult
    {
        $errors = [];

        // Check if 3DS is required based on amount threshold (example: €30)
        if ($transaction->shopperInteraction === ShopperInteraction::ECOMMERCE) {
            if ($transaction->total !== null) {
                $amount = (float) $transaction->total->amount;

                // Strong Customer Authentication (SCA) threshold
                if ($amount > 30.00 && $transaction->total->currencyCode === Currency::EUR) {
                    if ($transaction->threeDSecure === null) {
                        $errors[] = new ValidationError(
                            '3ds.requiredForAmount',
                            'Transactions over €30 require 3D Secure authentication (PSD2/SCA)',
                            'threeDSecure'
                        );
                    }
                }
            }
        }

        return new ValidationResult($errors);
    }
}
```

### Rule 4: Subscription Validation

```php
class SubscriptionValidator
{
    public function validate(Subscription $subscription): ValidationResult
    {
        $errors = [];

        // Subscriptions must have a stored payment method
        if ($subscription->storedCard === null && $subscription->storedAchPayment === null) {
            $errors[] = new ValidationError(
                'subscription.paymentMethodRequired',
                'Subscriptions require a stored payment method (storedCard or storedAchPayment)',
                'storedCard'
            );
        }

        // Subscriptions must have a shopper
        if ($subscription->shopper === null) {
            $errors[] = new ValidationError(
                'subscription.shopperRequired',
                'Subscriptions must be associated with a shopper',
                'shopper'
            );
        }

        // Subscriptions must have a plan
        if ($subscription->plan === null) {
            $errors[] = new ValidationError(
                'subscription.planRequired',
                'Subscriptions must reference a billing plan',
                'plan'
            );
        }

        return new ValidationResult($errors);
    }
}
```

### Rule 5: Order Validation

```php
class OrderValidator
{
    public function validate(Order $order): ValidationResult
    {
        $errors = [];

        // Orders must have at least one item
        if ($order->items === null || count($order->items) === 0) {
            $errors[] = new ValidationError(
                'order.itemsRequired',
                'Orders must contain at least one item',
                'items'
            );
        }

        // Order total should match sum of items (warning, not error)
        if ($order->items !== null && $order->total !== null) {
            $calculatedTotal = $this->calculateItemsTotal($order->items);

            if (bccomp($calculatedTotal, $order->total->amount, 2) !== 0) {
                $errors[] = new ValidationError(
                    'order.totalMismatch',
                    sprintf(
                        'Order total (%s) does not match sum of items (%s)',
                        $order->total->amount,
                        $calculatedTotal
                    ),
                    'total',
                    severity: 'warning' // Non-fatal
                );
            }
        }

        return new ValidationResult($errors);
    }

    private function calculateItemsTotal(array $items): string
    {
        $total = '0';
        foreach ($items as $item) {
            $itemTotal = bcmul($item->amount->amount, (string) $item->quantity, 2);
            $total = bcadd($total, $itemTotal, 2);
        }
        return $total;
    }
}
```

---

## Validation Helper Classes

### ValidationResult Class

```php
namespace Academe\Elavon\Epg\Psr7\Validation;

class ValidationResult
{
    /**
     * @param array<ValidationError> $errors
     */
    public function __construct(
        private readonly array $errors = []
    ) {}

    /**
     * Check if validation passed (no errors).
     */
    public function isValid(): bool
    {
        return empty($this->getErrors());
    }

    /**
     * Get all errors.
     *
     * @return array<ValidationError>
     */
    public function getErrors(): array
    {
        return array_filter(
            $this->errors,
            fn(ValidationError $e) => $e->severity === 'error'
        );
    }

    /**
     * Get all warnings (non-fatal).
     *
     * @return array<ValidationError>
     */
    public function getWarnings(): array
    {
        return array_filter(
            $this->errors,
            fn(ValidationError $e) => $e->severity === 'warning'
        );
    }

    /**
     * Get all issues (errors + warnings).
     *
     * @return array<ValidationError>
     */
    public function getAllIssues(): array
    {
        return $this->errors;
    }

    /**
     * Get error messages only.
     *
     * @return array<string>
     */
    public function getErrorMessages(): array
    {
        return array_map(
            fn(ValidationError $e) => $e->message,
            $this->getErrors()
        );
    }

    /**
     * Get errors grouped by field.
     *
     * @return array<string, array<ValidationError>>
     */
    public function getErrorsByField(): array
    {
        $grouped = [];
        foreach ($this->errors as $error) {
            $field = $error->field ?? '_general';
            $grouped[$field][] = $error;
        }
        return $grouped;
    }
}
```

### ValidationError Class

```php
namespace Academe\Elavon\Epg\Psr7\Validation;

class ValidationError
{
    public function __construct(
        public readonly string $code,
        public readonly string $message,
        public readonly ?string $field = null,
        public readonly string $severity = 'error', // 'error' or 'warning'
    ) {}

    /**
     * Convert to array for JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
            'field' => $this->field,
            'severity' => $this->severity,
        ];
    }
}
```

---

## Testing Strategy

### Unit Tests for DTO Validation

```php
class ContactTest extends TestCase
{
    public function test_construct_withInvalidCountryCode_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Country code must be exactly 3 characters');

        new Contact(countryCode: 'US'); // Only 2 characters
    }

    public function test_construct_withValidCountryCode_createsInstance(): void
    {
        $contact = new Contact(countryCode: 'USA');

        $this->assertSame('USA', $contact->countryCode);
    }
}
```

### Integration Tests for Business Validators

```php
class TransactionValidatorTest extends TestCase
{
    private TransactionValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new TransactionValidator();
    }

    public function test_validate_refundWithoutParent_returnsError(): void
    {
        $transaction = new Transaction(
            type: TransactionType::REFUND,
            total: new Money('10.00', Currency::USD),
            parentTransaction: null, // Missing!
        );

        $result = $this->validator->validate($transaction);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
        $this->assertSame('refund.parentRequired', $result->getErrors()[0]->code);
    }

    public function test_validate_cardTransactionWithCardData_passes(): void
    {
        $transaction = new Transaction(
            paymentMethod: PaymentMethod::CARD,
            card: new Card(
                pan: '4111111111111111',
                expirationMonth: '12',
                expirationYear: '2025'
            ),
        );

        $result = $this->validator->validate($transaction);

        $this->assertTrue($result->isValid());
    }
}
```

---

## Summary

### Validation Decision Matrix

| Validation Type | Layer | Throws Exception | Optional |
|----------------|-------|------------------|----------|
| Format validation | DTO | Yes | No |
| Length constraints | DTO | Yes | No |
| Enum validation | DTO | Yes | No |
| Required fields for operation | Request | Yes | No |
| Payment method rules | Business | No (returns ValidationResult) | Yes |
| Transaction type rules | Business | No (returns ValidationResult) | Yes |
| Cross-field dependencies | Business | No (returns ValidationResult) | Yes |
| Merchant-specific rules | Business | No (returns ValidationResult) | Yes |

### Key Principles

1. **Fail Fast at DTO Level** - Invalid DTOs cannot be constructed
2. **Operation-Specific at Request Level** - Each request validates its own requirements
3. **Optional at Business Level** - Applications choose which business rules to enforce
4. **Clear Error Messages** - All validation failures provide actionable error messages
5. **Testable** - Each validation rule can be unit tested independently

---

## Implementation Checklist

- [x] DTO validation pattern established (existing DTOs)
- [x] Request validation pattern established (CreateTransactionRequest)
- [ ] Create `ValidationResult` class
- [ ] Create `ValidationError` class
- [ ] Create `TransactionValidator` class
- [ ] Create `PaymentMethodValidator` class
- [ ] Create `TransactionTypeValidator` class
- [ ] Create `ThreeDSecureValidator` class
- [ ] Create `SubscriptionValidator` class
- [ ] Create `OrderValidator` class
- [ ] Document validation patterns in README
- [ ] Add validation examples to documentation
- [ ] Write comprehensive tests for all validators
