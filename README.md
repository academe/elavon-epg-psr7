# Elavon EPG PSR-7

PSR-7 HTTP messages and Data Transfer Object (DTO) classes for the Elavon Payment Gateway (EPG) API.

The [API is described here](https://developer.elavon.com/products/en-uk/elavon-payment-gateway/v1/api-reference#section/Overview/Credentials) and the [OpenAPI descrition is here](./docs/openapi.json)

## Overview

This package provides strongly-typed PHP classes for interacting with the Elavon Payment Gateway API. It includes:

- PSR-7 compliant HTTP message implementations
- DTO classes for request and response data structures
- Type-safe value objects for payment data
- Support for all EPG API resources and operations

This package handles message construction and serialization. A separate HTTP client package will handle the actual sending of requests.

## Get an Elavon Merchant Account

Don't have an Elavon merchant account yet? [**Sign up here**](https://www.elavon.eu/partner-form.html?partner_id=0014H00004E0iYw) to get started. Elavon's team will contact you within 24-48 hours to discuss your payment processing needs.

## Requirements

- PHP 8.1 or higher
- An [Elavon merchant account](https://www.elavon.eu/partner-form.html?partner_id=0014H00004E0iYw) with API credentials
- PSR-7 HTTP Message implementation (or use built-in message)
- PSR-17 HTTP Factory implementation (or user built-in factory)

## Installation

```bash
composer require academe/elavon-epg-psr7
```

## Quick Start

Here's a complete example creating a credit card payment using Guzzle:

```php
<?php

use Academe\Elavon\Epg\Psr7\Messages\Request\Transaction\CreateTransactionRequest;
use Academe\Elavon\Epg\Psr7\Messages\Response\Transaction\TransactionResponse;
use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
use GuzzleHttp\Client;

// 1. Configure the API factory (reusable for multiple requests)
$factory = ElavonApiFactory::configure()
    ->withRegion('eu')                                    // 'eu' or 'us'
    ->withEnvironment('sandbox')                          // 'sandbox' or 'live'
    ->withAuthentication($merchantAlias, $apiSecret);     // Your credentials

// 2. Create the transaction request
$request = new CreateTransactionRequest([
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
]);

// 3. Build and apply API configuration
$psr7Request = $factory->apply($request->build());

// 4. Send with Guzzle (or any PSR-18 client)
$client = new Client();
$psr7Response = $client->sendRequest($psr7Request);

// 5. Parse the response
$response = TransactionResponse::fromPsr7Response($psr7Response);

if ($response->isSuccessful()) {
    $transaction = $response->getTransaction();
    echo "Transaction ID: {$transaction->id}\n";
    echo "State: {$transaction->state->value}\n";
} else {
    $error = $response->getError();
    echo "Error: {$error->getMessage()}\n";
}
```

### Using DTOs (fromData)

You can create DTOs explicitly from array data using `fromData()`:

```php
use Academe\Elavon\Epg\Psr7\Dtos\Transaction;

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

$request = new CreateTransactionRequest($transaction);
```

### Using Value Objects (Type-Safe)

For full type safety, construct DTOs with typed value objects:

```php
use Academe\Elavon\Epg\Psr7\Dtos\Transaction;
use Academe\Elavon\Epg\Psr7\Dtos\Card;
use Academe\Elavon\Epg\Psr7\ValueObjects\Money;
use Academe\Elavon\Epg\Psr7\Enums\Currency;

$transaction = new Transaction(
    total: new Money('99.99', Currency::USD),
    card: new Card(
        number: '4111111111111111',
        securityCode: '123',
        expirationMonth: 12,
        expirationYear: 2025,
        holderName: 'John Doe',
    ),
    description: 'Order #12345',
);

$request = new CreateTransactionRequest($transaction);
```

See [docs/examples/](docs/examples/) for more examples.

## Features

Based on the Elavon Payment Gateway API version 2025-10-01, this package supports:

### Resources
- Merchants
- Processor Accounts
- Terminals
- Accounts
- Orders
- Payment Links & Payment Link Events
- Payment Sessions
- Payment Method Links & Payment Method Sessions
- Apple Pay Payment Sessions
- Hosted Cards & Hosted ACH Payments
- HSM Cards
- Forex Advices
- Transactions
- Apple Pay, Google Pay, and Paze Payments
- Surcharge & Refund Surcharge Advices
- Batches & Manual Batches
- Shoppers
- Stored Cards & Stored ACH Payments
- Plans & Subscriptions
- Notifications
- Total Adjustments

## API Documentation

The Elavon Payment Gateway API documentation is available at:
- Sandbox: https://uat.api.converge.eu.elavonaws.com
- Production: https://api.eu.convergepay.com

## Development

### Testing

```bash
composer test
```

### Code Quality

```bash
composer phpstan
composer cs-check
composer cs-fix
```

## License

MIT License. See [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome. Please ensure all tests pass and code follows PSR-12 coding standards.

## Namespace

All classes are under the `Academe\Elavon\Epg\Psr7\` namespace.
