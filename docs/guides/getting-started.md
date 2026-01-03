# Getting Started

## Introduction

Welcome to the Elavon EPG PSR-7 package! This guide will help you understand how to use this package to construct PSR-7 messages for the Elavon Payment Gateway API.

## Installation

```bash
composer require academe/elavon-epg-psr7
```

## Package Purpose

This package provides:

- **PSR-7 HTTP Messages**: Request/response implementations for EPG API
- **Data Transfer Objects (DTOs)**: Strongly-typed classes for API resources
- **Value Objects**: Immutable types for payment data (EmailAddress, IpAddress, Url, etc.)
- **Enums**: Type-safe enumerations for fixed value sets

**Note**: This package handles message construction only. You need a PSR-18 HTTP client (like Guzzle) to send the requests to the Elavon Payment Gateway.

## Quick Example

Here's a basic example of creating a transaction request:

```php
<?php

declare(strict_types=1);

use Academe\Elavon\Epg\Psr7\Dtos\Transaction;
use Academe\Elavon\Epg\Psr7\Dtos\Card;
use Academe\Elavon\Epg\Psr7\Messages\Request\Transaction\CreateTransactionRequest;
use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
use Money\Money;

// Create a Money value object (using moneyphp/money)
$total = Money::USD(9999); // Amount in minor units (cents)

// Create a Card data object
$card = new Card(
    number: '4111111111111111',
    expirationMonth: 12,
    expirationYear: 2025,
    securityCode: '123',
    holderName: 'John Doe',
);

// Create a Transaction data object
$transaction = new Transaction(
    total: $total,
    card: $card,
);

// Build the PSR-7 request
$request = new CreateTransactionRequest($transaction);
$psr7Request = $request->build();

// Configure and apply API settings
$factory = ElavonApiFactory::configure()
    ->withRegion('eu')
    ->withEnvironment('sandbox')
    ->withAuthentication($merchantAlias, $apiSecret);

$psr7Request = $factory->apply($psr7Request);

// Now you can send $psr7Request with any PSR-18 HTTP client
```

## Core Concepts

### Value Objects

Value objects represent domain concepts as immutable types:

```php
use Academe\Elavon\Epg\Psr7\ValueObjects\EmailAddress;
use Academe\Elavon\Epg\Psr7\ValueObjects\IpAddress;

// EmailAddress - validates format
$email = new EmailAddress('customer@example.com');

// IpAddress - validates format
$ip = new IpAddress('192.168.1.1');
```

### Data Transfer Objects (DTOs)

DTOs represent API resources with strongly-typed properties:

```php
use Academe\Elavon\Epg\Psr7\Dtos\Transaction;
use Academe\Elavon\Epg\Psr7\Enums\TransactionState;

// Create from array data
$transaction = Transaction::fromData([
    'id' => 'txn_abc123',
    'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
    'state' => 'AUTHORIZED',
]);

// Convert to array for serialization
$data = $transaction->toData();
```

### Enumerations

PHP 8.1+ backed enums for type-safe value sets:

```php
use Academe\Elavon\Epg\Psr7\Enums\TransactionState;

// Transaction states
$state = TransactionState::AUTHORIZED;
$state = TransactionState::DECLINED;
$state = TransactionState::CAPTURED;

// Enums are type-safe
function processTransaction(TransactionState $state): void {
    match ($state) {
        TransactionState::AUTHORIZED => handleAuthorized(),
        TransactionState::DECLINED => handleDeclined(),
        TransactionState::CAPTURED => handleCaptured(),
        default => handleOther(),
    };
}
```

### Money Handling

This project uses the `moneyphp/money` library for monetary values:

```php
use Money\Money;
use Money\Currency;

// Create money in minor units
$amount = Money::USD(9999);  // $99.99

// Or with Currency object
$amount = new Money('5000', new Currency('GBP'));  // £50.00

// Money is immutable
$newAmount = $amount->add(Money::USD(100));

// Access values
$minorUnits = $amount->getAmount();  // "9999"
$currency = $amount->getCurrency()->getCode();  // "USD"
```

### PSR-7 Messages

Request and response messages following PSR-7 standard:

```php
use Academe\Elavon\Epg\Psr7\Messages\Request\Transaction\CreateTransactionRequest;
use Academe\Elavon\Epg\Psr7\Messages\Response\Transaction\TransactionResponse;

// Create a request message
$request = new CreateTransactionRequest($transaction);
$psr7Request = $request->build();

// The PSR-7 request can be sent using any PSR-18 HTTP client
$response = $httpClient->sendRequest($psr7Request);

// Parse the response
$transactionResponse = TransactionResponse::fromPsr7Response($response);

if ($transactionResponse->isSuccessful()) {
    $transaction = $transactionResponse->transaction;
} else {
    $error = $transactionResponse->error;
}
```

## Next Steps

1. Read [Architecture Documentation](../architecture.md) to understand the package design
2. Explore [Coding Standards](../coding-standards.md) if you want to contribute
3. Check [Examples](../examples/) for more usage patterns

## Requirements

- **PHP**: 8.1 or higher
- **moneyphp/money**: For monetary values
- **PSR-7**: HTTP message interfaces (included)
- **PSR-17**: HTTP factory interfaces (included)

## Development

```bash
# Install dependencies
composer install

# Run tests
composer test

# Static analysis
composer phpstan

# Code style check
composer cs-check

# Code style fix
composer cs-fix
```

## Support & Resources

- **Documentation**: See [docs/](../) directory
- **Issues**: [GitHub Issues](https://github.com/academe/elavon-epg-psr7/issues)
- **Contributing**: See [CONTRIBUTING.md](../../CONTRIBUTING.md)

## License

MIT License - see [LICENSE](../../LICENSE) for details.
