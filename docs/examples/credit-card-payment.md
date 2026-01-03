# Credit Card Payment Example

This example shows how to create a PSR-7 request for a credit card payment transaction.

## Basic Usage

```php
<?php

use Academe\Elavon\Epg\Psr7\Messages\Request\Transaction\CreateTransactionRequest;
use Academe\Elavon\Epg\Psr7\Messages\Response\Transaction\TransactionResponse;
use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;

// 1. Configure the API factory
$factory = ElavonApiFactory::configure()
    ->withRegion('eu')
    ->withEnvironment('sandbox')
    ->withAuthentication($merchantAlias, $apiSecret);

// 2. Create the request using fromData() with arrays (simplest approach)
$request = CreateTransactionRequest::fromData([
    'transaction' => [
        'total' => [
            'amount' => '99.99',
            'currencyCode' => 'USD',
        ],
        'card' => [
            'number' => '4111111111111111',
            'securityCode' => '123',
            'expirationMonth' => 12,
            'expirationYear' => 2025,
            'holderName' => 'John Doe',
        ],
        'description' => 'Order #12345',
    ],
]);

// 3. Build and apply API configuration
$psr7Request = $factory->apply($request->build());

// 4. Send with any PSR-18 HTTP client
$httpClient = new \GuzzleHttp\Client();
$psr7Response = $httpClient->sendRequest($psr7Request);

// 5. Parse the response
$response = TransactionResponse::fromPsr7Response($psr7Response);

if ($response->isSuccessful()) {
    $transaction = $response->transaction;
    echo "Transaction ID: " . $transaction->id . "\n";
    echo "State: " . $transaction->state->value . "\n";
    echo "Amount: " . $transaction->total->getAmount() . " (minor units)\n";
    echo "Currency: " . $transaction->total->getCurrency()->getCode() . "\n";
} else {
    echo "Transaction failed with status: " . $response->statusCode . "\n";
    if ($response->error) {
        echo "Error: " . $response->error->getMessage() . "\n";
    }
}
```

## Option 2: Using Value Objects (type-safe)

```php
<?php

use Academe\Elavon\Epg\Psr7\Dtos\Transaction;
use Academe\Elavon\Epg\Psr7\Dtos\Card;
use Academe\Elavon\Epg\Psr7\Messages\Request\Transaction\CreateTransactionRequest;
use Money\Money;

// Create Money value object (using moneyphp/money)
$total = Money::USD(9999); // Amount in minor units (cents)

// Create Card DTO
$card = new Card(
    number: '4111111111111111',
    securityCode: '123',
    expirationMonth: 12,
    expirationYear: 2025,
    holderName: 'John Doe',
);

// Create Transaction DTO
$transaction = new Transaction(
    total: $total,
    card: $card,
    description: 'Order #12345',
);

// Create request with the Transaction object
$request = new CreateTransactionRequest($transaction);

$psr7Request = $request->build();
```

## Option 3: Using Transaction::fromData()

```php
<?php

use Academe\Elavon\Epg\Psr7\Dtos\Transaction;
use Academe\Elavon\Epg\Psr7\Messages\Request\Transaction\CreateTransactionRequest;

// Create Transaction from array data
$transaction = Transaction::fromData([
    'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
    'card' => [
        'number' => '4111111111111111',
        'securityCode' => '123',
        'expirationMonth' => 12,
        'expirationYear' => 2025,
        'holderName' => 'John Doe',
    ],
    'description' => 'Order #12345',
]);

// Create request with the Transaction object
$request = new CreateTransactionRequest($transaction);
```

## Validation

All validation happens automatically in constructors:

```php
<?php

use Academe\Elavon\Epg\Psr7\Dtos\Card;
use Academe\Elavon\Epg\Psr7\Dtos\Transaction;

// Invalid card number - throws InvalidArgumentException
$card = new Card(
    number: '123',  // Too short - must be 13-19 digits
    securityCode: '123',
    expirationMonth: 12,
    expirationYear: 2025,
);

// Invalid expiration month - throws InvalidArgumentException
$card = new Card(
    number: '4111111111111111',
    securityCode: '123',
    expirationMonth: 13,  // Must be 1-12
    expirationYear: 2025,
);

// Invalid transaction total - throws InvalidArgumentException
$transaction = Transaction::fromData([
    'total' => ['amount' => '-10.00', 'currencyCode' => 'USD'],  // Must be positive
    'card' => [...],
]);
```

## Working with Responses

```php
<?php

use Academe\Elavon\Epg\Psr7\Messages\Response\Transaction\TransactionResponse;
use Academe\Elavon\Epg\Psr7\Enums\TransactionState;

$response = TransactionResponse::fromPsr7Response($psr7Response);

// Access transaction data via public readonly property
$transaction = $response->transaction;

// Response fields
echo "Transaction ID: " . $transaction->id . "\n";
echo "State: " . $transaction->state->value . "\n";
echo "Created: " . $transaction->createdAt->format('Y-m-d H:i:s') . "\n";

// Money values (using moneyphp/money)
echo "Amount: " . $transaction->total->getAmount() . " (minor units)\n";
echo "Currency: " . $transaction->total->getCurrency()->getCode() . "\n";

// Card details from response
if ($transaction->card !== null) {
    echo "Card Last 4: " . $transaction->card->last4 . "\n";
    echo "Card Scheme: " . $transaction->card->scheme->value . "\n";
    echo "Card BIN: " . $transaction->card->bin . "\n";
}

// Check transaction state
match ($transaction->state) {
    TransactionState::AUTHORIZED => handleAuthorized($transaction),
    TransactionState::DECLINED => handleDeclined($transaction),
    TransactionState::CAPTURED => handleCaptured($transaction),
    default => handleUnknownState($transaction),
};
```

## Using ElavonApiFactory

The `ElavonApiFactory` handles authentication and environment configuration:

```php
<?php

use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;

$factory = ElavonApiFactory::configure()
    ->withRegion('eu')           // 'eu' or 'us'
    ->withEnvironment('sandbox') // 'sandbox', 'test', 'live', 'production'
    ->withAuthentication($merchantAlias, $apiSecret);

// Apply to any request
$psr7Request = $factory->apply($request->build());

// The factory adds:
// - Base URL for the region/environment
// - Authorization header (Basic auth)
// - Accept and Content-Type headers
// - API version header
```

## Test Cards

For testing in the sandbox environment:

- **Successful**: 4111111111111111
- **Declined**: 4000000000000002
- **Expired**: Use any expired date

See the [Elavon EPG documentation](https://developer.elavon.com/) for more test cards.

## Error Handling

```php
<?php

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Transaction\TransactionResponse;

// Validation errors (thrown during construction)
try {
    $transaction = Transaction::fromData([
        'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
        'card' => ['number' => 'invalid'],
    ]);
} catch (InvalidArgumentException $e) {
    echo "Validation error: " . $e->getMessage() . "\n";
}

// Response parsing errors
try {
    $response = TransactionResponse::fromPsr7Response($psr7Response);
} catch (InvalidArgumentException $e) {
    echo "Response parse error: " . $e->getMessage() . "\n";
}

// API errors (non-2xx responses)
$response = TransactionResponse::fromPsr7Response($psr7Response);
if ($response->hasError()) {
    $error = $response->error;
    echo "API Error: " . $error->getMessage() . "\n";
    echo "Error Code: " . $error->getCode() . "\n";

    foreach ($error->getFailures() as $failure) {
        echo "- " . $failure->description . "\n";
    }
}
```

## Using Custom PSR-17 Factories (Optional)

The package includes a built-in PSR-7/PSR-17 implementation, but you can use your own:

```php
<?php

use Academe\Elavon\Epg\Psr7\Dtos\Transaction;
use Academe\Elavon\Epg\Psr7\Messages\Request\Transaction\CreateTransactionRequest;

// Using nyholm/psr7
$factory = new \Nyholm\Psr7\Factory\Psr17Factory();

$transaction = Transaction::fromData([...]);
$request = new CreateTransactionRequest($transaction);
$request->setRequestFactory($factory);
$request->setStreamFactory($factory);

$psr7Request = $request->build();
```

**When to use custom factories:**

- Your framework/app already has PSR-17 factories configured
- You need specific features from a particular implementation
- Testing with mocked factories

**When to use built-in (default):**

- Simple use cases
- Minimal dependencies
- Quick prototyping
