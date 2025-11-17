# Data Transfer Object (DTO) Architecture

## Overview

The DTO architecture provides a data-driven approach to serialization and deserialization that can be reused across all DTOs in the codebase. This eliminates code duplication and makes it easy to add new DTOs.

## Components

### 1. ValueObject Interface

**Location**: `src/Contracts/ValueObject.php`

Defines the base contract for value objects and DTOs that can be serialized to/from arrays:

```php
interface ValueObject
{
    public static function fromData(mixed $data): static;
    public function toData(): mixed;
}
```

This simpler interface is used by value objects like `Money` that don't need the full property type system.

### 2. DataTransferObject Interface

**Location**: `src/Contracts/DataTransferObject.php`

Extends `ValueObject` with additional metadata requirements for the SerializesData trait:

```php
interface DataTransferObject extends ValueObject
{
    public static function getPropertyTypes(): array;
    public function toObjectArray(): array;
}
```

All DTOs implement this interface, which includes:
- `fromData()` and `toData()` from `ValueObject`
- `getPropertyTypes()` for property type metadata
- `toObjectArray()` for shallow object representation

**When to use which interface:**
- Use `ValueObject` for simple value objects with custom serialization (e.g., `Money`)
- Use `DataTransferObject` for DTOs that benefit from the property type system and SerializesData trait

### 3. SerializesData Trait

**Location**: `src/Concerns/SerializesData.php`

Provides reusable implementations of `fromData()`, `toData()`, and `toObjectArray()` based on property type definitions from `getPropertyTypes()`.

**Key Features:**
- Data-driven serialization/deserialization
- Supports multiple property types (money, object, enum, string, boolean, int, array)
- Handles type casting automatically
- Zero code duplication across DTOs

### 4. Property Type System

DTOs define their property types using the `getPropertyTypes()` method:

```php
public static function getPropertyTypes(): array
{
    return [
        'money' => ['total', 'totalRefunded'],
        'object' => ['card', 'shipTo', 'billTo'],
        'array' => ['failures', 'tags'],
        'enum' => ['state', 'type', 'paymentMethod'],
        'string' => ['id', 'description', 'customReference'],
        'boolean' => ['isAuthorized', 'isVoided'],
        'int' => ['status', 'expirationMonth', 'expirationYear'],
    ];
}
```

### Supported Property Types

| Type | Description | Example | Serialization Behavior |
|------|-------------|---------|------------------------|
| `money` | Money value objects | `total`, `tip` | Calls `toData()` on Money object |
| `object` | Other DTOs | `card`, `shipTo` | Calls `toData()` on nested DTO |
| `array` | Arrays of objects or primitives | `failures`, `tags` | Calls `toData()` on objects, passes through primitives |
| `enum` | PHP 8.1 backed enums | `state`, `type` | Converts to `value` property (string) |
| `string` | String primitives | `id`, `description` | Cast to string if present |
| `boolean` | Boolean primitives | `isAuthorized` | Cast to bool if present |
| `int` | Integer primitives | `status`, `expirationMonth` | Cast to int if present |

## Usage

### Creating a New DTO

1. **Implement the interface**:
```php
use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;

class MyDTO implements DataTransferObject
{
    use SerializesData;

    // ... property declarations
}
```

2. **Define property types**:
```php
public static function getPropertyTypes(): array
{
    return [
        'money' => ['amount'],
        'string' => ['id', 'name'],
        'boolean' => ['isActive'],
    ];
}
```

3. **Declare properties and constructor**:
```php
public function __construct(
    Money|array|null $amount = null,
    public readonly ?string $id = null,
    public readonly ?string $name = null,
    public readonly ?bool $isActive = null,
) {
    // Normalize Money objects
    $this->amount = match (true) {
        $amount instanceof Money => $amount,
        is_array($amount) => Money::fromData($amount),
        default => null,
    };
}
```

That's it! The `fromData()`, `toData()`, and `toObjectArray()` methods are automatically provided by the trait.

## Example: Transaction DTO

```php
class Transaction implements DataTransferObject
{
    use SerializesData;

    public static function getPropertyTypes(): array
    {
        return [
            'money' => ['total', 'totalRefunded', 'tip'],
            'object' => ['card', 'shipTo', 'billTo'],
            'array' => ['failures'],
            'enum' => ['state', 'type', 'paymentMethod'],
            'string' => ['id', 'description'],
            'boolean' => ['isAuthorized', 'isVoided'],
        ];
    }

    public function __construct(
        Money|array|null $total = null,
        Card|array|null $card = null,
        ?array $failures = null,
        TransactionState|string|null $state = null,
        public readonly ?string $id = null,
        public readonly ?bool $isAuthorized = null,
        // ... other properties
    ) {
        // Normalize objects and enums
        // ...
    }
}
```

## Benefits

✅ **Zero Duplication** - Serialization logic written once, reused everywhere
✅ **Type Safety** - Explicit categorization ensures correct handling
✅ **Maintainable** - Adding properties only requires updating `getPropertyTypes()`
✅ **Consistent** - All DTOs behave the same way
✅ **Flexible** - Easy to handle API inconsistencies by overriding trait methods
✅ **Testable** - Trait can be tested independently

## API Inconsistencies

If the API uses the same property name inconsistently (different types in different contexts), you can override the trait method in your DTO:

```php
class MyDTO implements DataTransferObject
{
    use SerializesData;

    // Override if needed for special cases
    public function toData(): mixed
    {
        $data = parent::toData();

        // Custom handling for inconsistent API field
        if ($this->specialField !== null) {
            $data['specialField'] = $this->customTransform($this->specialField);
        }

        return $data;
    }
}
```

## Migration Guide

To migrate an existing DTO to use this architecture:

1. Implement `DataTransferObject` interface
2. Add `use SerializesData;` trait
3. Implement `getPropertyTypes()` method
4. Remove `fromData()`, `toData()`, and `toObjectArray()` methods (now provided by trait)
5. Run tests to verify behavior is unchanged
