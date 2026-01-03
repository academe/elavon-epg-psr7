# Credit Card Payment Vertical Stack

This document describes the complete implementation of the credit card payment vertical stack.

## Overview

A fully working set of classes for creating PSR-7 messages to process credit card payments through the Elavon Payment Gateway API.

## Components Created

### 1. Enums

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

### 2. Money Handling

This project uses the `moneyphp/money` package for all monetary values:

```php
use Money\Money;
use Money\Currency;

// Create money in minor units (cents)
$total = Money::USD(9999);  // $99.99

// Or with Currency object
$amount = new Money('5000', new Currency('GBP'));  // £50.00
```

### 3. Data Transfer Objects (DTOs)

**[src/Dtos/Card.php](../src/Dtos/Card.php)**

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
- **Auto-validation**: All validation in constructor

**[src/Dtos/Transaction.php](../src/Dtos/Transaction.php)**

- Represents a payment transaction
- **Request fields**:
  - `total` - Money\Money object (required, must be positive)
  - `card` - Card object (required for card payments)
  - `description` - Optional description
  - `customReference` - Optional merchant reference
- **Response fields**:
  - `id` - Transaction ID
  - `state` - TransactionState enum
  - `createdAt` - DateTimeImmutable
- Uses `SerializesData` trait for automatic type conversion

### 4. PSR-7 Messages

**[src/Messages/Request/Transaction/CreateTransactionRequest.php](../src/Messages/Request/Transaction/CreateTransactionRequest.php)**

- Builds PSR-7 request for `POST /transactions`
- Accepts Transaction object or array via `fromData()`
- Serializes to JSON
- Returns PSR-7 RequestInterface ready to send

**[src/Messages/Response/Transaction/TransactionResponse.php](../src/Messages/Response/Transaction/TransactionResponse.php)**

- Parses PSR-7 response from EPG API
- Deserializes JSON to Transaction object
- Properties:
  - `transaction` - Parsed Transaction DTO (on success)
  - `error` - ErrorResponse DTO (on failure)
  - `statusCode` - HTTP status code
- Methods:
  - `isSuccessful()` - Check if 2xx status
  - `hasError()` - Check if error exists

### 5. Exceptions

**[src/Exceptions/EpgException.php](../src/Exceptions/EpgException.php)**

- Base exception interface

**[src/Exceptions/InvalidArgumentException.php](../src/Exceptions/InvalidArgumentException.php)**

- Validation failures
- Invalid data formats

## Usage Patterns

### Pattern 1: Simple Arrays with fromData()

```php
use Academe\Elavon\Epg\Psr7\Messages\Request\Transaction\CreateTransactionRequest;

$request = CreateTransactionRequest::fromData([
    'transaction' => [
        'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
        'card' => [
            'number' => '4111111111111111',
            'securityCode' => '123',
            'expirationMonth' => 12,
            'expirationYear' => 2025,
            'holderName' => 'John Doe',
        ],
    ],
]);

$psr7Request = $request->build();
```

### Pattern 2: Type-Safe Objects

```php
use Academe\Elavon\Epg\Psr7\Dtos\Transaction;
use Academe\Elavon\Epg\Psr7\Dtos\Card;
use Academe\Elavon\Epg\Psr7\Messages\Request\Transaction\CreateTransactionRequest;
use Money\Money;

$total = Money::USD(9999); // $99.99 in minor units
$card = new Card(
    number: '4111111111111111',
    securityCode: '123',
    expirationMonth: 12,
    expirationYear: 2025,
    holderName: 'John Doe',
);
$transaction = new Transaction(total: $total, card: $card);

$request = new CreateTransactionRequest($transaction);
$psr7Request = $request->build();
```

### Pattern 3: Mixed (Flexibility)

```php
use Academe\Elavon\Epg\Psr7\Dtos\Transaction;
use Academe\Elavon\Epg\Psr7\Dtos\Card;

$transaction = Transaction::fromData([
    'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
    'card' => [
        'number' => '4111111111111111',
        'securityCode' => '123',
        'expirationMonth' => 12,
        'expirationYear' => 2025,
    ],
]);
```

## Validation

All validation happens automatically:

### Money Validation

- Uses `moneyphp/money` library for precision
- Business rule: Transaction total must be positive

### Card Validation

- Card number: 13-19 digits (allows spaces/dashes)
- Security code: 3-4 digits
- Expiration month: 1-12
- Expiration year: 2000-2099

### Automatic Type Coercion

- Arrays → DTOs via `fromData()`
- Validation on construction
- Clear error messages

## Complete Flow Example

```php
use Academe\Elavon\Epg\Psr7\Messages\Request\Transaction\CreateTransactionRequest;
use Academe\Elavon\Epg\Psr7\Messages\Response\Transaction\TransactionResponse;
use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
use Academe\Elavon\Epg\Psr7\Enums\TransactionState;

// 1. Configure the API factory
$factory = ElavonApiFactory::configure()
    ->withRegion('eu')
    ->withEnvironment('sandbox')
    ->withAuthentication($merchantAlias, $apiSecret);

// 2. Create transaction request from array data
$request = CreateTransactionRequest::fromData([
    'transaction' => [
        'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
        'card' => [
            'number' => '4111111111111111',
            'securityCode' => '123',
            'expirationMonth' => 12,
            'expirationYear' => 2025,
        ],
    ],
]);

// 3. Build and apply API configuration
$psr7Request = $factory->apply($request->build());

// 4. Send with any PSR-18 HTTP client
$httpClient = new YourHttpClient();
$psr7Response = $httpClient->sendRequest($psr7Request);

// 5. Parse response
$response = TransactionResponse::fromPsr7Response($psr7Response);

// 6. Handle result
if ($response->isSuccessful()) {
    $transaction = $response->transaction;

    match ($transaction->state) {
        TransactionState::AUTHORIZED => handleSuccess($transaction),
        TransactionState::DECLINED => handleDecline($transaction),
        default => handleOther($transaction),
    };
} else {
    $error = $response->error;
    echo "Error: {$error->getMessage()}\n";
}
```

## What's NOT Included

This vertical stack focuses on credit card payments. Not included:

- Other payment methods (ACH, Apple Pay, Google Pay, etc.)
- Advanced features (3-D Secure, tokenization, subscriptions)
- Capture/void/refund operations
- Batch operations
- Authentication (handled by ElavonApiFactory)
- HTTP client (use any PSR-18 client)

## Testing

See [examples/credit-card-payment.md](examples/credit-card-payment.md) for:

- Full usage examples
- Test card numbers
- Error handling
- Response parsing

## Architecture Benefits

- **Developer-friendly**: Accept simple arrays via `fromData()`
- **Type-safe internally**: Everything validated
- **Flexible**: Mix arrays and objects as needed
- **PSR compliant**: Works with any PSR-18 HTTP client
- **Testable**: Easy to unit test with mocked factories

## Next Steps

To expand the package:

1. Add other payment methods (ACH, wallets)
2. Add transaction operations (capture, void, refund)
3. Add advanced features (subscriptions, batches)
4. Add more DTOs (merchants, accounts, etc.)

Each can be added as a separate vertical stack following the same patterns.
