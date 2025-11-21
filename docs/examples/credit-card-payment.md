# Credit Card Payment Example

This example shows how to create a PSR-7 request for a credit card payment transaction.

## Basic Usage (No Dependencies Required!)

```php
<?php

use Academe\Elavon\Epg\Psr7\Messages\Request\Transaction\CreateTransactionRequest;
use Academe\Elavon\Epg\Psr7\Messages\Response\Transaction\TransactionResponse;

// Option 1: Using arrays (simplest - uses built-in PSR-7 factory)
$request = new CreateTransactionRequest(
    transaction: [
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
);

// Build the PSR-7 request (uses built-in factory)
$psr7Request = $request->build();

// Send with any PSR-18 HTTP client
$httpClient = new \YourChoice\HttpClient();
$psr7Response = $httpClient->sendRequest($psr7Request);

// Parse the response
$response = TransactionResponse::fromPsr7Response($psr7Response);

if ($response->isSuccessful()) {
    $transaction = $response->getTransaction();
    echo "Transaction ID: " . $transaction->id . "\n";
    echo "State: " . $transaction->state->value . "\n";
    echo "Amount: " . $transaction->total->amount . " " . $transaction->total->currency->value . "\n";
} else {
    echo "Transaction failed with status: " . $response->getStatusCode() . "\n";
}
```

## Option 2: Using Value Objects (type-safe)

```php
<?php

// Create Money value object
$total = new Money('99.99', Currency::USD);

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

// Create request
$request = new CreateTransactionRequest(
    transaction: $transaction,
    requestFactory: $requestFactory,
    streamFactory: $streamFactory,
);

$psr7Request = $request->build();
```

## Option 3: Mixed Approach

```php
<?php

// Mix arrays and objects as needed
$transaction = new Transaction(
    total: ['amount' => '99.99', 'currencyCode' => 'USD'],  // Array
    card: new Card(                                          // Object
        number: '4111111111111111',
        securityCode: '123',
        expirationMonth: 12,
        expirationYear: 2025,
    ),
);
```

## Validation

All validation happens automatically in constructors:

```php
<?php

// Invalid card number - throws InvalidArgumentException
$card = new Card(
    number: '123',  // Too short
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
$transaction = new Transaction(
    total: ['amount' => '-10.00', 'currencyCode' => 'USD'],  // Must be positive
    card: $card,
);
```

## Working with Responses

```php
<?php

$response = TransactionResponse::fromPsr7Response($psr7Response);

// Access transaction data
$transaction = $response->getTransaction();

// Response fields (read-only from API)
echo "Transaction ID: " . $transaction->id . "\n";
echo "State: " . $transaction->state->value . "\n";
echo "Created: " . $transaction->createdAt . "\n";

// Card details from response
if ($transaction->card !== null) {
    echo "Card Last 4: " . $transaction->card->last4 . "\n";
    echo "Card Scheme: " . $transaction->card->scheme->value . "\n";
    echo "Card BIN: " . $transaction->card->bin . "\n";
}

// Check transaction state
use Academe\Elavon\Epg\Psr7\Enums\TransactionState;

match ($transaction->state) {
    TransactionState::AUTHORIZED => handleAuthorized($transaction),
    TransactionState::DECLINED => handleDeclined($transaction),
    TransactionState::CAPTURED => handleCaptured($transaction),
    default => handleUnknownState($transaction),
};
```

## Authentication

The request does not include authentication headers. You should add these when sending:

```php
<?php

$psr7Request = $request->build()
    ->withHeader('Authorization', 'Basic ' . base64_encode($merchantAlias . ':' . $apiKey));

// Or using your HTTP client's authentication
$httpClient = new Client([
    'auth' => [$merchantAlias, $apiKey],
]);
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

try {
    $transaction = new Transaction(
        total: ['amount' => '99.99', 'currencyCode' => 'USD'],
        card: ['number' => 'invalid'],
    );
} catch (InvalidArgumentException $e) {
    echo "Validation error: " . $e->getMessage() . "\n";
}

try {
    $response = TransactionResponse::fromPsr7Response($psr7Response);
} catch (InvalidArgumentException $e) {
    echo "Response parse error: " . $e->getMessage() . "\n";
}
```

## Using Custom PSR-17 Factories (Optional)

The package includes a built-in PSR-7/PSR-17 implementation, but you can use your own if you prefer:

```php
<?php

// Using nyholm/psr7
$factory = new \Nyholm\Psr7\Factory\Psr17Factory();

$request = new CreateTransactionRequest(
    transaction: [...],
    requestFactory: $factory,
    streamFactory: $factory,
);

// Using guzzlehttp/psr7
$requestFactory = new \GuzzleHttp\Psr7\HttpFactory();
$streamFactory = new \GuzzleHttp\Psr7\HttpFactory();

$request = new CreateTransactionRequest(
    transaction: [...],
    requestFactory: $requestFactory,
    streamFactory: $streamFactory,
);
```

**When to use custom factories:**

- Your framework/app already has PSR-17 factories configured
- You need specific features from a particular implementation
- Testing with mocked factories
- Performance optimization with a specific library

**When to use built-in (default):**

- Simple use cases
- Minimal dependencies
- Quick prototyping
- No special requirements