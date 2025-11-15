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

## Requirements

- PHP 8.1 or higher
- PSR-7 HTTP Message implementation
- PSR-17 HTTP Factory implementation

## Installation

```bash
composer require academe/elavon-epg-psr7
```

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

## Usage

Documentation and examples will be provided as the package develops.

```php
use Academe\Elavon\Epg\Psr7\Messages\CreateTransactionRequest;
use Academe\Elavon\Epg\Psr7\DataObjects\Transaction;

// Example usage will be documented here
```

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