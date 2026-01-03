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

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Enums\TransactionType;
use Money\Money;

/**
 * Represents a transaction in the EPG API.
 *
 * Uses the moneyphp/money package for monetary values.
 * The SerializesData trait uses reflection to automatically handle
 * type conversion for Money, enums, and nested DTOs.
 */
class Transaction implements DataTransferObject
{
    use SerializesData;

    public function __construct(
        public readonly ?string $id = null,
        public readonly ?Money $total = null,
        public readonly ?TransactionType $type = null,
    ) {}
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
class CardNumber {}
class EmailAddress {}
class ExpiryDate {}

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
public \Money\Money $total;
public ?Card $card;

// ✗ Avoid untyped properties
public $id;
public $total;
```

### Method Signatures

```php
// ✓ Explicit types
public function calculateTotal(\Money\Money $amount, \Money\Money $tax): \Money\Money
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
final readonly class CardNumber
{
    public function __construct(
        public string $value,
    ) {}

    public function mask(): string
    {
        // Return new value, don't modify
        return str_repeat('*', strlen($this->value) - 4) . substr($this->value, -4);
    }
}

// ✗ Mutable
class CardNumber
{
    public string $value;

    public function setValue(string $value): void
    {
        $this->value = $value; // Mutation!
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
        public \Money\Money $total,
        public TransactionState $state,
    ) {}
}

// Create new instance for changes
$captured = new Transaction(
    ...$transaction->toArray(),
    state: TransactionState::CAPTURED,
);
```

### Money Values

This project uses the `moneyphp/money` package for all monetary values:

```php
use Money\Money;
use Money\Currency;

// ✓ Use Money\Money for monetary values
final class Order
{
    public function __construct(
        public readonly ?Money $total = null,
        public readonly ?Money $salesTax = null,
    ) {}
}

// Creating Money objects - use factory methods
$total = Money::USD(9999);  // $99.99 in minor units
$tax = Money::EUR(1050);    // €10.50 in minor units

// Or with Currency object
$amount = new Money('5000', new Currency('GBP'));  // £50.00

// ✓ Money is immutable - operations return new instances
$newTotal = $total->add($tax);
$doubled = $total->multiply(2);

// ✓ Get amount (returns string of minor units)
$minorUnits = $total->getAmount();  // "9999"

// ✓ Get currency
$code = $total->getCurrency()->getCode();  // "USD"
```

### Money Serialization in DTOs

The `SerializesData` trait handles Money serialization automatically using reflection:

```php
use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Money\Money;

class Order implements DataTransferObject
{
    use SerializesData;

    public function __construct(
        public readonly ?Money $total = null,
    ) {}
}

// fromData() parses API format to Money\Money automatically
// The trait detects Money type via reflection and converts
$order = Order::fromData([
    'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
]);
// $order->total is Money\Money with amount "9999" (minor units)

// You can also use minor units directly:
$order = Order::fromData([
    'total' => ['amountMinor' => 9999, 'currencyCode' => 'USD'],
]);

// toData() serializes Money\Money to API format (major units)
$data = $order->toData();
// $data['total'] = ['amount' => '99.99', 'currencyCode' => 'USD']
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
    $money1 = Money::USD(1000);  // $10.00
    $money2 = Money::USD(550);   // $5.50

    // Act
    $result = $money1->add($money2);

    // Assert
    $this->assertSame('1550', $result->getAmount());
    $this->assertSame('USD', $result->getCurrency()->getCode());
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
