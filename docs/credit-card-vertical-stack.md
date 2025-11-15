# Credit Card Payment Vertical Stack

This document describes the complete implementation of the credit card payment vertical stack.

## Overview

A fully working set of classes for creating PSR-7 messages to process credit card payments through the Elavon Payment Gateway API.

## Components Created

### 1. Enums

**[src/Enums/Currency.php](../src/Enums/Currency.php)** (Already existed)
- ISO 4217 currency codes
- 170+ currencies supported

**[src/Enums/TransactionState.php](../src/Enums/TransactionState.php)**
```php
enum TransactionState: string {
    AUTHORIZATION_PENDING
    AUTHORIZED
    DECLINED
    CAPTURED
    SETTLED
    REFUNDED
    VOIDED
    FAILED
    UNKNOWN
}
```

**[src/Enums/CardScheme.php](../src/Enums/CardScheme.php)**
```php
enum CardScheme: string {
    AMERICAN_EXPRESS
    DINERS_CLUB
    DISCOVER
    JCB
    MAESTRO
    MASTERCARD
    UNION_PAY
    VISA
    UNKNOWN
}
```

### 2. Value Objects

**[src/ValueObjects/Money.php](../src/ValueObjects/Money.php)** (Already existed)
- Represents monetary amounts with currency
- Validation: 9 integer digits max, 4 fractional digits max
- Methods: `isPositive()`, `isNegative()`, `isZero()`, `equals()`, `negate()`

### 3. Data Transfer Objects (DTOs)

**[src/DataObjects/Card.php](../src/DataObjects/Card.php)**
- Represents card details for requests and responses
- **Request fields** (writeOnly):
  - `number` - Card PAN (validated: 13-19 digits)
  - `securityCode` - CVV/CVC (validated: 3-4 digits)
  - `expirationMonth` - 1-12
  - `expirationYear` - 2000-2099
  - `holderName` - Cardholder name
- **Response fields** (readOnly):
  - `last4` - Last 4 digits
  - `bin` - Bank identification number
  - `scheme` - Card scheme (Visa, MasterCard, etc.)
  - `fingerprint` - Card fingerprint
- **Flexible constructor**: Accepts arrays or pre-built objects
- **Auto-validation**: All validation in constructor

**[src/DataObjects/Transaction.php](../src/DataObjects/Transaction.php)**
- Represents a payment transaction
- **Request fields**:
  - `total` - Money object (required, must be positive)
  - `card` - Card object (required for card payments)
  - `description` - Optional description
  - `customReference` - Optional merchant reference
- **Response fields**:
  - `id` - Transaction ID
  - `state` - TransactionState enum
  - `createdAt` - ISO 8601 timestamp
- **Flexible constructor**: Accepts Money/Card objects or arrays
- **Auto-normalization**: Converts arrays to value objects

### 4. PSR-7 Messages

**[src/Messages/Request/CreateTransactionRequest.php](../src/Messages/Request/CreateTransactionRequest.php)**
- Builds PSR-7 request for `POST /transactions`
- Accepts Transaction object or array
- Serializes to JSON
- Returns PSR-7 RequestInterface ready to send

**[src/Messages/Response/TransactionResponse.php](../src/Messages/Response/TransactionResponse.php)**
- Parses PSR-7 response from EPG API
- Deserializes JSON to Transaction object
- Helper methods:
  - `getTransaction()` - Get parsed Transaction
  - `isSuccessful()` - Check if 2xx status
  - `getStatusCode()` - Get HTTP status

### 5. Exceptions

**[src/Exceptions/EpgException.php](../src/Exceptions/EpgException.php)** (Already existed)
- Base exception interface

**[src/Exceptions/InvalidArgumentException.php](../src/Exceptions/InvalidArgumentException.php)** (Already existed)
- Validation failures
- Invalid data formats

## Usage Patterns

### Pattern 1: Simple Arrays (Most Developer-Friendly)

```php
$request = new CreateTransactionRequest(
    transaction: [
        'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
        'card' => [
            'number' => '4111111111111111',
            'securityCode' => '123',
            'expirationMonth' => 12,
            'expirationYear' => 2025,
        ],
    ],
    requestFactory: $requestFactory,
    streamFactory: $streamFactory,
);

$psr7Request = $request->build();
```

### Pattern 2: Type-Safe Objects

```php
$money = new Money('99.99', Currency::USD);
$card = new Card(
    number: '4111111111111111',
    securityCode: '123',
    expirationMonth: 12,
    expirationYear: 2025,
);
$transaction = new Transaction(total: $money, card: $card);

$request = new CreateTransactionRequest(
    transaction: $transaction,
    requestFactory: $requestFactory,
    streamFactory: $streamFactory,
);
```

### Pattern 3: Mixed (Flexibility)

```php
$transaction = new Transaction(
    total: ['amount' => '99.99', 'currencyCode' => 'USD'],  // Array
    card: new Card(...),                                     // Object
);
```

## Validation

All validation happens automatically:

### Money Validation
- Amount format: max 9 integer digits, max 4 fractional
- Must be numeric
- Business rule: Transaction total must be positive

### Card Validation
- Card number: 13-19 digits (allows spaces/dashes)
- Security code: 3-4 digits
- Expiration month: 1-12
- Expiration year: 2000-2099

### Automatic Type Coercion
- Arrays → Value Objects
- Validation on construction
- Clear error messages

## Complete Flow Example

```php
// 1. Create transaction with arrays (simple)
$request = new CreateTransactionRequest(
    transaction: [
        'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
        'card' => [
            'number' => '4111111111111111',
            'securityCode' => '123',
            'expirationMonth' => 12,
            'expirationYear' => 2025,
        ],
    ],
    requestFactory: $requestFactory,
    streamFactory: $streamFactory,
);

// 2. Build PSR-7 request
$psr7Request = $request->build();

// 3. Add authentication (your HTTP client handles this)
$psr7Request = $psr7Request->withHeader(
    'Authorization',
    'Basic ' . base64_encode($merchantAlias . ':' . $apiKey)
);

// 4. Send with any PSR-18 HTTP client
$httpClient = new YourHttpClient();
$psr7Response = $httpClient->sendRequest($psr7Request);

// 5. Parse response
$response = TransactionResponse::fromPsr7Response($psr7Response);

// 6. Handle result
if ($response->isSuccessful()) {
    $transaction = $response->getTransaction();

    match ($transaction->state) {
        TransactionState::AUTHORIZED => handleSuccess($transaction),
        TransactionState::DECLINED => handleDecline($transaction),
        default => handleOther($transaction),
    };
}
```

## What's NOT Included

This vertical stack focuses on credit card payments. Not included:
- Other payment methods (ACH, Apple Pay, Google Pay, etc.)
- Advanced features (3-D Secure, tokenization, subscriptions)
- Capture/void/refund operations
- Batch operations
- Authentication (handled by HTTP client)
- HTTP client (use any PSR-18 client)

## Testing

See [examples/credit-card-payment.md](examples/credit-card-payment.md) for:
- Full usage examples
- Test card numbers
- Error handling
- Response parsing

## Architecture Benefits

✅ **Developer-friendly**: Accept simple arrays
✅ **Type-safe internally**: Everything validated
✅ **Flexible**: Mix arrays and objects as needed
✅ **No inheritance**: Classes can be extended if needed
✅ **PSR compliant**: Works with any PSR-18 HTTP client
✅ **Testable**: Easy to unit test with mocked factories

## Next Steps

To expand the package:
1. Add other payment methods (ACH, wallets)
2. Add transaction operations (capture, void, refund)
3. Add advanced features (subscriptions, batches)
4. Add more DTOs (merchants, accounts, etc.)

Each can be added as a separate vertical stack following the same patterns.