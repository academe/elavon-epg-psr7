# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PSR-7 HTTP messages and Data Transfer Objects (DTOs) for the Elavon Payment Gateway (EPG) API. This package handles message construction and serialization for the payment API - it does NOT send requests (use a PSR-18 client like Guzzle for that).

Namespace: `Academe\Elavon\Epg\Psr7\`

## Commands

```bash
# Run all unit tests
composer test

# Run a specific test file
vendor/bin/phpunit tests/Unit/Dtos/TransactionTest.php

# Run a specific test method
vendor/bin/phpunit --filter=test_method_name

# Static analysis (level 8)
composer phpstan

# Code style check/fix (PSR-12)
composer cs-check
composer cs-fix
```

## Architecture

### OpenAPI Description

The source OpenAPI description is at `docs\openapi.json`.
This file is too big to read in one go, so has been split up for parsing contextually as needed under `docs\openapi-split`. It is the split version that should be used.

### Core Interfaces

- **ValueObject** (`Contracts/ValueObject.php`): Base interface for serializable objects with `fromData()` and `toData()` methods
- **DataTransferObject** (`Contracts/DataTransferObject.php`): Extends ValueObject, adds `toObjectArray()` for shallow array conversion
- **RequestMessage** (`Contracts/RequestMessage.php`): Marker for request builders that produce PSR-7 requests via `build()`
- **ResponseMessage** (`Contracts/ResponseMessage.php`): Marker for response parsers with `isSuccessful()` and `fromPsr7Response()`

### Data Serialization Pattern

The `SerializesData` trait (`Concerns/SerializesData.php`) provides reflection-based serialization:

- **fromData(array)**: Creates DTOs from JSON-compatible arrays. Uses reflection to inspect constructor parameters and auto-converts:
  - Scalars (string, int, bool, float)
  - DateTimeImmutable (from ISO 8601 strings)
  - Money objects (from `['amount' => '99.99', 'currencyCode' => 'USD']` or `['amountMinor' => 9999, ...]`)
  - BackedEnums (from string values)
  - Nested DTOs/VOs (via their `fromData()` methods)
  - Arrays with `#[ArrayOf(ClassName::class)]` attribute

- **toData()**: Converts back to JSON-compatible arrays, recursively converting nested objects

### Array Type Hints

Use the `#[ArrayOf(Type::class)]` attribute on constructor parameters to specify array element types:

```php
#[ArrayOf(OrderItem::class)]
public readonly ?array $items = null,

#[ArrayOf(CardBrand::class)]  // enums work too
public readonly ?array $supportedBrands = null,
```

### Request/Response Pattern

**Requests** (`Messages/Request/{Resource}/`):
- Constructor takes a DTO or primitive values
- `fromData(array)` factory creates from raw arrays
- `build()` returns a PSR-7 `RequestInterface` with JSON body

**Responses** (`Messages/Response/{Resource}/`):
- Use `ParsesPsr7Response` trait
- `fromPsr7Response(ResponseInterface)` parses JSON and status code
- `isSuccessful()` checks for 2xx status
- Contains typed DTO property on success, `ErrorResponse` on failure

### ElavonApiFactory

Configures requests with environment, region, and authentication:

```php
$factory = ElavonApiFactory::configure()
    ->withRegion('eu')           // 'eu' or 'us'
    ->withEnvironment('sandbox') // 'sandbox' or 'live'
    ->withAuthentication($merchantAlias, $apiSecret);

$request = $factory->apply($request->build());  // Adds headers and base URL
```

### DTOs vs Value Objects

- **DTOs** (`Dtos/`): Complex objects with multiple properties (Transaction, Order, Card). Use `SerializesData` trait, implement `DataTransferObject`.
- **Value Objects** (`ValueObjects/`): Simple validated wrappers (EmailAddress, IpAddress, Url). Implement `ValueObject` directly, serialize to primitives.

### Money Handling

Uses `moneyphp/money` library. The API expects amounts in major units (decimal strings like "99.99"), but Money objects store minor units internally. The `SerializesData` trait handles conversion automatically:

```php
// Input (API format - major units)
['amount' => '99.99', 'currencyCode' => 'USD']

// Or (convenience format - minor units)
['amountMinor' => 9999, 'currencyCode' => 'USD']

// Output (always major units for API compatibility)
['amount' => '99.99', 'currencyCode' => 'USD']
```

## File Organization

- `src/Dtos/` - Data Transfer Objects (Transaction, Order, Card, etc.)
- `src/ValueObjects/` - Simple validated value wrappers
- `src/Enums/` - Backed enums for API constants
- `src/Messages/Request/` - Request builders organized by resource
- `src/Messages/Response/` - Response parsers organized by resource
- `src/Concerns/` - Reusable traits (SerializesData)
- `src/Contracts/` - Interfaces
- `src/Support/` - Utilities (ElavonApiFactory, PSR-17 implementations)
- `src/Attributes/` - PHP attributes (ArrayOf)
- `src/Exceptions/` - Exception classes