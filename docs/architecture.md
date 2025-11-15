# Package Architecture

## Overview

The `academe/elavon-epg-psr7` package provides a clean, type-safe PHP implementation for constructing Elavon Payment Gateway API requests and parsing responses. It follows SOLID principles and leverages PHP 8.1+ features for enhanced type safety.

## Design Philosophy

### 1. Separation of Concerns
- **Message Construction**: This package (PSR-7 messages and DTOs)
- **HTTP Transport**: Separate package will handle actual API communication
- **Business Logic**: Consumer applications handle payment workflows

### 2. Type Safety
- Leverage PHP 8.1+ typed properties and union types
- Use enums for fixed value sets
- Immutable value objects for data integrity
- Strict type declarations throughout

### 3. PSR Compliance
- **PSR-7**: HTTP message interfaces
- **PSR-17**: HTTP factory interfaces
- **PSR-12**: Extended coding style
- **PSR-4**: Autoloading

## Directory Structure

```
src/
├── Messages/           # PSR-7 request/response implementations
│   ├── Request/       # API request messages
│   └── Response/      # API response messages
├── DataObjects/       # DTOs for complex data structures
│   ├── Transaction/   # Transaction-related DTOs
│   ├── Payment/       # Payment method DTOs
│   ├── Shopper/       # Shopper-related DTOs
│   └── ...
├── ValueObjects/      # Immutable value objects
│   ├── Money.php
│   ├── Currency.php
│   ├── CardNumber.php
│   └── ...
├── Enums/             # Enumeration types
│   ├── TransactionState.php
│   ├── PaymentMethod.php
│   ├── PaymentMethodOrigin.php
│   └── ...
├── Contracts/         # Interfaces
│   ├── MessageInterface.php
│   ├── SerializableInterface.php
│   └── ...
├── Exceptions/        # Custom exceptions
│   ├── InvalidArgumentException.php
│   ├── ValidationException.php
│   └── ...
└── Support/           # Helper classes and traits
    ├── Traits/
    └── Helpers/
```

## Core Components

### Value Objects

Immutable objects representing domain concepts:

```php
namespace Academe\Elavon\Epg\Psr7\ValueObjects;

/**
 * PHP 8.1 compatible: Use readonly on individual properties
 * (Class-level readonly requires PHP 8.2+)
 */
final class Money
{
    public function __construct(
        public readonly string $amount,
        public readonly Currency $currency,
    ) {}
}
```

**Characteristics:**
- Immutable (readonly properties - PHP 8.1+)
- Validate on construction
- Rich comparison and formatting methods
- No setters, only factory methods for transformations

### Data Transfer Objects (DTOs)

Structured data containers for API requests/responses:

```php
namespace Academe\Elavon\Epg\Psr7\DataObjects;

final class Transaction
{
    public function __construct(
        public readonly string $id,
        public readonly Money $total,
        public readonly TransactionState $state,
        public readonly ?Card $card = null,
        // ... more properties
    ) {}

    public function toArray(): array { /* ... */ }
    public static function fromArray(array $data): self { /* ... */ }
}
```

**Characteristics:**
- Readonly by default
- Type-safe properties
- Serialization methods (toArray, fromArray)
- Optional properties with null defaults
- Validation in constructor

### Enumerations

PHP 8.1+ backed enums for fixed value sets:

```php
namespace Academe\Elavon\Ept\Psr7\Enums;

enum TransactionState: string
{
    case AUTHORIZED = 'authorized';
    case DECLINED = 'declined';
    case CAPTURED = 'captured';
    case REFUNDED = 'refunded';
    // ...
}
```

### PSR-7 Messages

Request messages for API endpoints:

```php
namespace Academe\Elavon\Ept\Psr7\Messages\Request;

use Psr\Http\Message\RequestInterface;

final class CreateTransactionRequest implements RequestInterface
{
    public function __construct(
        private Transaction $transaction,
        private RequestFactoryInterface $requestFactory,
    ) {}

    public function build(): RequestInterface
    {
        return $this->requestFactory
            ->createRequest('POST', '/transactions')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(/* serialized transaction */);
    }
}
```

## Naming Conventions

### Classes
- **Value Objects**: Singular nouns (e.g., `Money`, `CardNumber`)
- **DTOs**: Match API resource names (e.g., `Transaction`, `Shopper`)
- **Enums**: Singular nouns describing the type (e.g., `TransactionState`)
- **Requests**: `{Verb}{Resource}Request` (e.g., `CreateTransactionRequest`)
- **Responses**: `{Resource}Response` (e.g., `TransactionResponse`)

### Methods
- **Factories**: `create()`, `from{Source}()` (e.g., `fromArray()`)
- **Serialization**: `toArray()`, `toJson()`
- **Validation**: `validate()`, `isValid()`
- **Comparison**: `equals()`, `isSameAs()`

## API Resource Mapping

Each EPG API resource maps to corresponding classes:

| API Resource | DTO Class | Request Messages | Response Messages |
|--------------|-----------|------------------|-------------------|
| Transaction | `DataObjects\Transaction` | `CreateTransactionRequest`<br>`UpdateTransactionRequest` | `TransactionResponse` |
| Shopper | `DataObjects\Shopper` | `CreateShopperRequest`<br>`UpdateShopperRequest` | `ShopperResponse` |
| Payment Link | `DataObjects\PaymentLink` | `CreatePaymentLinkRequest` | `PaymentLinkResponse` |
| ... | ... | ... | ... |

## Error Handling

### Exception Hierarchy

```
Exception
└── Academe\Elavon\Ept\Psr7\Exceptions\EpgException (interface)
    ├── InvalidArgumentException
    ├── ValidationException
    ├── SerializationException
    └── MessageConstructionException
```

### Validation Strategy

1. **Constructor validation**: Immediate validation of required fields
2. **Type safety**: Leverage PHP type system first
3. **Domain validation**: Business rule validation in value objects
4. **Fail fast**: Throw exceptions early, not at serialization time

## Immutability Pattern

All DTOs and value objects are immutable:

```php
// ✓ Correct: Create new instance
$newTransaction = new Transaction(
    ...$transaction->toArray(),
    state: TransactionState::CAPTURED,
);

// ✗ Wrong: Mutation not possible
$transaction->state = TransactionState::CAPTURED; // Error!
```

## Testing Strategy

### Unit Tests
- Value object behavior
- DTO serialization/deserialization
- Enum cases and methods
- Message construction

### Integration Tests
- Full request/response cycles (mocked)
- Serialization roundtrips
- Complex nested structures

### Test Structure
```
tests/
├── Unit/
│   ├── ValueObjects/
│   ├── DataObjects/
│   ├── Enums/
│   └── Messages/
├── Integration/
└── Fixtures/
```

## Future Considerations

### Extensibility Points
- Custom validation rules via interfaces
- Pluggable serializers
- Event hooks for message construction
- Middleware for request/response transformation

### Versioning
- Follow semantic versioning strictly
- Maintain backward compatibility within major versions
- Deprecation notices before breaking changes
- API version mapping support

## Related Documentation

- [Coding Standards](coding-standards.md)
- [Contributing Guidelines](../CONTRIBUTING.md)
- [Getting Started Guide](guides/getting-started.md)