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
- **Value Objects**: Immutable types for payment data (Money, CardNumber, etc.)
- **Enums**: Type-safe enumerations for fixed value sets

**Note**: This package handles message construction only. A separate package will handle the actual HTTP communication with the Elavon Payment Gateway.

## Quick Example

Here's a basic example of creating a transaction request:

```php
<?php

declare(strict_types=1);

use Academe\Elavon\Epg\Psr7\Dtos\Transaction;
use Academe\Elavon\Epg\Psr7\Dtos\Card;
use Academe\Elavon\Epg\Psr7\Messages\Request\Transaction\CreateTransactionRequest;
use Money\Money;

// Create a Money value object (using moneyphp/money)
$total = Money::USD(9999); // Amount in cents

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

// Now you can pass $psr7Request to your HTTP client
```

## Core Concepts

### Value Objects

Value objects represent domain concepts as immutable types:

```php
// Money - represents an amount with currency
$amount = new Money('50.00', Currency::EUR);

// All value objects are readonly and immutable
$newAmount = $amount->add(new Money('10.00', Currency::EUR));
```

See [Value Objects Guide](value-objects.md) for more details.

### Data Transfer Objects (DTOs)

DTOs represent API resources with strongly-typed properties:

```php
// Transaction DTO
$transaction = new Transaction(
    id: 'txn_abc123',
    total: $total,
    state: TransactionState::AUTHORIZED,
    card: $card,
);

// Convert to array for serialization
$data = $transaction->toData();

// Create from API response
$transaction = Transaction::fromData($responseData);
```

See [DTO Classes Guide](dto-classes.md) for more details.

### Enumerations

PHP 8.1+ backed enums for type-safe value sets:

```php
// Transaction states
$state = TransactionState::AUTHORIZED;
$state = TransactionState::DECLINED;
$state = TransactionState::CAPTURED;

// Payment methods
$method = PaymentMethod::CARD;
$method = PaymentMethod::ACH;
$method = PaymentMethod::WALLET;

// Enums are type-safe
function processTransaction(TransactionState $state): void {
    match ($state) {
        TransactionState::AUTHORIZED => // ...
        TransactionState::DECLINED => // ...
        TransactionState::CAPTURED => // ...
    };
}
```

### PSR-7 Messages

Request and response messages following PSR-7 standard:

```php
// Create a request message
$request = new CreateTransactionRequest($transaction);
$psr7Request = $request->build();

// The PSR-7 request can be sent using any PSR-18 HTTP client
$response = $httpClient->sendRequest($psr7Request);

// Parse the response
$transactionResponse = TransactionResponse::fromPsr7Response($response);
```

See [PSR-7 Messages Guide](psr7-messages.md) for more details.

## Next Steps

1. Read [Architecture Documentation](../architecture.md) to understand the package design
2. Explore [Coding Standards](../coding-standards.md) if you want to contribute
3. Check [Examples](../examples/) for more usage patterns
4. Review the [OpenAPI Specification](../openapi.json) for complete API reference

## Requirements

- **PHP**: 8.1 or higher
- **PSR-7**: HTTP message interfaces implementation
- **PSR-17**: HTTP factory interfaces implementation

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

## API Version

This package is based on the Elavon Payment Gateway API version **2025-10-01**.

## License

MIT License - see [LICENSE](../../LICENSE) for details.