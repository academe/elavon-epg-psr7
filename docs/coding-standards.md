# Coding Standards

## Overview

This document defines the coding standards for the Elavon EPT PSR-7 package. All contributions must adhere to these guidelines.

## PHP Standards

### PSR Compliance

This project follows:
- **PSR-1**: Basic Coding Standard
- **PSR-12**: Extended Coding Style Guide
- **PSR-4**: Autoloading Standard

### PHP Version

- **Minimum**: PHP 8.1
- **Target**: PHP 8.1+
- Leverage modern PHP features (enums, readonly, union types, etc.)

## Code Style

### General Rules

```php
<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\ValueObjects;

use Academe\Elavon\Epg\Psr7\Enums\Currency;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * Represents a monetary value with currency.
 *
 * Uses individual readonly properties for PHP 8.1 compatibility.
 * (Class-level readonly was introduced in PHP 8.2)
 */
final class Money
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly string $amount,
        public readonly Currency $currency,
    ) {
        if (!$this->isValidAmount($amount)) {
            throw new InvalidArgumentException('Invalid amount format');
        }
    }

    private function isValidAmount(string $amount): bool
    {
        return (bool) preg_match('/^\d+(\.\d{1,2})?$/', $amount);
    }
}
```

### Key Principles

1. **Strict types**: Always use `declare(strict_types=1);`
2. **Final by default**: Classes should be `final` unless designed for inheritance
3. **Readonly properties**: Use `readonly` for immutable objects
4. **Type hints**: Always type hint parameters and return types
5. **Named arguments**: Constructor parameters should be named for clarity

### Formatting

- **Indentation**: 4 spaces (no tabs)
- **Line length**: Soft limit 120 characters, hard limit 150
- **Blank lines**: One blank line between methods
- **Array syntax**: Short syntax `[]` not `array()`
- **String quotes**: Single quotes unless interpolation needed

### Naming Conventions

#### Classes
```php
// Value Objects - Singular, descriptive nouns
class Money {}
class CardNumber {}
class EmailAddress {}

// DTOs - Match API resource names
class Transaction {}
class Shopper {}
class PaymentLink {}

// Enums - Singular, represents the type
enum TransactionState {}
enum PaymentMethod {}

// Exceptions - End with "Exception"
class ValidationException {}
class InvalidArgumentException {}

// Traits - Adjective or "HasX" pattern
trait Serializable {}
trait HasMetadata {}

// Interfaces - End with "Interface"
interface SerializableInterface {}
interface MessageInterface {}
```

#### Methods
```php
// Factory methods - Static, prefixed with "create" or "from"
public static function createFromArray(array $data): self {}
public static function fromJson(string $json): self {}

// Serialization methods
public function toArray(): array {}
public function toJson(): string {}

// Validation methods
public function isValid(): bool {}
public function validate(): void {}

// Comparison methods
public function equals(self $other): bool {}
public function isSameAs(self $other): bool {}

// Boolean questions - Start with "is", "has", "can", "should"
public function isEmpty(): bool {}
public function hasCard(): bool {}
public function canCapture(): bool {}
```

#### Properties
```php
// Camel case
public string $firstName;
public ?CardNumber $cardNumber;
public TransactionState $state;

// Boolean properties - Prefix with "is", "has", "can"
public bool $isActive;
public bool $hasCustomer;
public bool $canRefund;
```

#### Constants
```php
// Upper case with underscores
public const MAX_RETRIES = 3;
public const API_VERSION = '2025-10-01';
```

## Type Safety

### Property Types

```php
// ✓ Always type properties
public string $id;
public Money $total;
public ?Card $card;

// ✗ Avoid untyped properties
public $id;
public $total;
```

### Method Signatures

```php
// ✓ Explicit types
public function calculateTotal(Money $amount, Money $tax): Money
{
    // ...
}

// ✗ Missing types
public function calculateTotal($amount, $tax)
{
    // ...
}
```

### Nullable Types

```php
// ✓ Use nullable when appropriate
public function __construct(
    public string $id,
    public ?string $description = null,
) {}

// ✗ Don't use mixed when possible
public function setDescription(mixed $description): void {}
```

### Union Types

```php
// ✓ Use union types for specific alternatives
public function setIdentifier(string|int $id): void {}

// ✓ Use for multiple value objects
public function setPaymentMethod(Card|AchPayment|WalletPayment $payment): void {}
```

## Documentation

### DocBlocks

```php
/**
 * Creates a transaction request for the EPG API.
 *
 * This class constructs a PSR-7 request message for creating
 * a new transaction through the Elavon Payment Gateway.
 *
 * @throws ValidationException When transaction data is invalid
 * @throws MessageConstructionException When request cannot be built
 */
final class CreateTransactionRequest implements RequestInterface
{
    /**
     * Builds the PSR-7 request message.
     *
     * @return RequestInterface The constructed request
     * @throws MessageConstructionException When serialization fails
     */
    public function build(): RequestInterface
    {
        // ...
    }
}
```

### Rules for DocBlocks

1. **Classes**: Describe purpose and usage
2. **Methods**: Describe behavior, parameters, return, exceptions
3. **Properties**: Only if not obvious from type
4. **Complex logic**: Inline comments for clarity

### PHPDoc Tags

- `@param` - Only if adding context beyond type
- `@return` - Only if adding context beyond type hint
- `@throws` - Always document exceptions
- `@deprecated` - When marking deprecated code

## Immutability

### Value Objects

```php
// ✓ Immutable with readonly
final readonly class Money
{
    public function __construct(
        public string $amount,
        public Currency $currency,
    ) {}

    public function add(Money $other): self
    {
        // Return new instance, don't modify
        return new self(
            bcadd($this->amount, $other->amount, 2),
            $this->currency,
        );
    }
}

// ✗ Mutable
class Money
{
    public string $amount;

    public function setAmount(string $amount): void
    {
        $this->amount = $amount; // Mutation!
    }
}
```

### DTOs

```php
// ✓ Readonly DTOs
final readonly class Transaction
{
    public function __construct(
        public string $id,
        public Money $total,
        public TransactionState $state,
    ) {}
}

// Create new instance for changes
$captured = new Transaction(
    ...$transaction->toArray(),
    state: TransactionState::CAPTURED,
);
```

## Error Handling

### Exception Types

```php
// Domain exceptions
throw new InvalidArgumentException('Card number is invalid');

// Validation failures
throw new ValidationException('Required field "total" is missing');

// Serialization issues
throw new SerializationException('Cannot encode transaction to JSON');
```

### Validation Approach

```php
// ✓ Fail fast in constructor
public function __construct(
    public string $email,
) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException("Invalid email: {$email}");
    }
}

// ✗ Don't defer validation
public function __construct(
    public string $email,
) {
    // No validation here - will fail later!
}
```

## Testing Standards

### Test Naming

```php
// Pattern: test_{method}_{scenario}_{expectedResult}
public function test_constructor_withInvalidAmount_throwsException(): void {}
public function test_add_withSameCurrency_returnsNewMoney(): void {}
public function test_toArray_withAllFields_returnsCompleteArray(): void {}
```

### Test Structure

```php
public function test_add_withSameCurrency_returnsNewMoney(): void
{
    // Arrange
    $money1 = new Money('10.00', Currency::USD);
    $money2 = new Money('5.50', Currency::USD);

    // Act
    $result = $money1->add($money2);

    // Assert
    $this->assertEquals('15.50', $result->amount);
    $this->assertSame(Currency::USD, $result->currency);
}
```

### Coverage Targets

- **Minimum**: 80% code coverage
- **Target**: 90%+ code coverage
- **Critical paths**: 100% coverage (value objects, validation)

## Static Analysis

### PHPStan

- **Level**: 8 (maximum)
- **Baseline**: Allowed for gradual improvements
- **CI**: Must pass before merge

### Configuration

See [phpstan.neon](../phpstan.neon) for project configuration.

## Code Review Checklist

Before submitting a PR, verify:

- [ ] All types are declared (properties, parameters, returns)
- [ ] Classes are `final` unless inheritance needed
- [ ] Value objects are `readonly`
- [ ] Validation happens in constructors
- [ ] Exceptions are documented with `@throws`
- [ ] Tests cover all public methods
- [ ] PHPStan level 8 passes
- [ ] Code style check passes (PSR-12)
- [ ] No TODOs or debug code

## IDE Configuration

### PhpStorm

1. Code Style: PSR-12
2. PHP Language Level: 8.1
3. Enable PHPStan inspection
4. Format on save: Yes

### VS Code

Install extensions:
- PHP Intelephense
- PHPStan
- PHP CS Fixer

See [.editorconfig](../.editorconfig) for editor settings.

## Resources

- [PSR-12 Specification](https://www.php-fig.org/psr/psr-12/)
- [PHP The Right Way](https://phptherightway.com/)
- [PHPStan Documentation](https://phpstan.org/user-guide/getting-started)