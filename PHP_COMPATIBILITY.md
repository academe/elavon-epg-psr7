# PHP Version Compatibility

## Requirement

**Minimum PHP Version**: 8.1

As specified in `composer.json`:
```json
{
  "require": {
    "php": "^8.1"
  }
}
```

## PHP 8.1 Features Used

The library uses PHP 8.1 features throughout:

### 1. Readonly Properties (PHP 8.1+)

Used for immutable DTOs:

```php
class ErrorDetail
{
    public function __construct(
        public readonly string $code,
        public readonly string $description,
        public readonly ?string $field = null,
    ) {}
}
```

**Note**: We use `readonly` on individual properties, NOT the `readonly class` modifier (which requires PHP 8.2+).

### 2. Named Arguments (PHP 8.0+)

Used extensively in constructors:

```php
$transaction = new Transaction(
    total: $money,
    card: $card,
    description: 'Order #123',
);
```

### 3. Union Types (PHP 8.0+)

```php
private readonly Transaction|array $transaction;
```

### 4. Constructor Property Promotion (PHP 8.0+)

```php
public function __construct(
    private readonly ResponseInterface $response,
) {}
```

### 5. Nullsafe Operator (PHP 8.0+)

```php
$scheme = $transaction->card?->scheme;
```

### 6. Match Expression (PHP 8.0+)

```php
match ($transaction->state) {
    TransactionState::AUTHORIZED => handleSuccess(),
    TransactionState::DECLINED => handleDecline(),
    default => handleOther(),
};
```

## PHP 8.2 Features NOT Used

To maintain PHP 8.1 compatibility, we specifically avoid:

### ❌ Readonly Classes (PHP 8.2+)

**Not used** (would break PHP 8.1):
```php
readonly class ErrorDetail { ... }
```

**Used instead** (PHP 8.1 compatible):
```php
class ErrorDetail
{
    public function __construct(
        public readonly string $code,
        public readonly string $description,
    ) {}
}
```

### ❌ Disjunctive Normal Form (DNF) Types (PHP 8.2+)

Not used - stick to simple union types.

## Testing Environment

Tests run under PHP 8.4.14 (as shown in PHPUnit output), but the code is compatible with PHP 8.1+.

## Upgrade Considerations

If you later decide to require PHP 8.2+, you can refactor:

```php
// Current (PHP 8.1 compatible)
class ErrorDetail
{
    public function __construct(
        public readonly string $code,
        public readonly string $description,
        public readonly ?string $field = null,
    ) {}
}

// Future (PHP 8.2+ only)
readonly class ErrorDetail
{
    public function __construct(
        public string $code,
        public string $description,
        public ?string $field = null,
    ) {}
}
```

The `readonly class` modifier applies readonly to all properties automatically, making the code slightly cleaner. However, this is purely syntactic sugar - functionality remains identical.

## Verification

To verify PHP 8.1 compatibility, you can run:

```bash
# Check minimum PHP version
composer check-platform-reqs

# Run PHPStan (will catch PHP version issues)
composer phpstan
```

## Summary

✅ **Fully compatible with PHP 8.1+**
✅ **Uses readonly properties** (individual property modifier, not class modifier)
✅ **No PHP 8.2+ features used**
✅ **Tests pass on PHP 8.4** (which includes all PHP 8.1 features)

The codebase is forward-compatible with newer PHP versions while maintaining PHP 8.1 as the minimum requirement.
