# API Package Architecture Guide

This document describes the architecture and design patterns used in the `academe/elavon-epg-psr7` package. Use this as a template for creating similar PSR-7 API client packages from OpenAPI specifications or textual API documentation.

## Table of Contents

1. [Design Philosophy](#design-philosophy)
2. [Package Overview](#package-overview)
3. [Directory Structure](#directory-structure)
4. [Core Concepts](#core-concepts)
5. [Component Patterns](#component-patterns)
6. [Serialization Strategy](#serialization-strategy)
7. [HTTP Message Building](#http-message-building)
8. [Error Handling](#error-handling)
9. [Testing Strategy](#testing-strategy)
10. [Creating a New API Package](#creating-a-new-api-package)

---

## Design Philosophy

### Key Principles

1. **PSR-7 First**: Build PSR-7 request messages without coupling to any specific HTTP client
2. **Type Safety**: Leverage PHP 8.1+ features (readonly properties, enums, union types)
3. **Immutability**: All DTOs and Value Objects are immutable
4. **Separation of Concerns**: Message construction separate from HTTP transport
5. **Minimal Dependencies**: Only PSR interfaces, no heavy frameworks

### What This Package Does

- Constructs PSR-7 HTTP request messages for API endpoints
- Parses PSR-7 HTTP response messages into typed objects
- Provides strongly-typed DTOs for request/response data
- Validates data on construction (fail-fast)
- Adds API-specific headers via decorator pattern

### What This Package Does NOT Do

- Send HTTP requests (use Guzzle, Symfony HttpClient, etc.)
- Handle retry logic or circuit breakers
- Implement OAuth flows or token refresh
- Cache responses

---

## Package Overview

```
composer.json requirements:
{
    "require": {
        "php": "^8.1",
        "psr/http-message": "^1.0|^2.0",
        "psr/http-factory": "^1.0"
    },
    "require-dev": {
        "guzzlehttp/guzzle": "^7.5|^8.0",      // For integration tests
        "nyholm/psr7": "^1.8",                  // Alternative PSR-7 implementation
        "phpunit/phpunit": "^10.0|^11.0",
        "phpstan/phpstan": "^1.10"
    }
}
```

---

## Directory Structure

```
src/
├── Concerns/                    # Reusable traits
│   └── SerializesData.php       # DTO serialization trait
├── Contracts/                   # Interfaces
│   ├── ValueObject.php          # Base interface for serializable objects
│   └── DataTransferObject.php   # Extended interface for complex DTOs
├── Dtos/                        # Data Transfer Objects
│   ├── Transaction.php
│   ├── Card.php
│   ├── Contact.php
│   ├── ErrorResponse.php
│   └── ...
├── Enums/                       # PHP 8.1+ backed enums
│   ├── TransactionState.php
│   ├── Currency.php
│   ├── PaymentMethod.php
│   └── ...
├── Exceptions/                  # Custom exceptions
│   ├── EpgException.php         # Base exception interface
│   └── InvalidArgumentException.php
├── Messages/                    # PSR-7 message builders
│   ├── Request/                 # Request message builders
│   │   ├── Transaction/
│   │   │   ├── CreateTransactionRequest.php
│   │   │   ├── RetrieveTransactionRequest.php
│   │   │   └── UpdateTransactionRequest.php
│   │   └── {Resource}/          # One folder per API resource
│   └── Response/                # Response message parsers
│       ├── Concerns/
│       │   └── HandlesErrors.php
│       └── Transaction/
│           ├── TransactionResponse.php
│           └── TransactionListResponse.php
├── Support/                     # Helper classes
│   ├── ElavonApiFactory.php     # API request factory
│   ├── Psr17Factory.php         # Built-in PSR-17 factory
│   ├── Request.php              # Built-in PSR-7 request
│   ├── Response.php             # Built-in PSR-7 response
│   ├── Stream.php               # Built-in PSR-7 stream
│   └── Uri.php                  # Built-in PSR-7 URI
└── ValueObjects/                # Immutable value objects
    ├── Money.php
    ├── EmailAddress.php
    ├── IpAddress.php
    └── ...

tests/
├── Unit/                        # Unit tests (no external calls)
│   ├── Dtos/
│   ├── Enums/
│   ├── Messages/
│   └── ValueObjects/
├── Integration/                 # Integration tests (real API calls)
│   └── OpayoIntegrationTest.php
└── Fixtures/                    # JSON fixtures for testing
```

---

## Core Concepts

### 1. Contracts/Interfaces

#### ValueObject Interface

The simplest contract - for objects that serialize to/from JSON-compatible data:

```php
namespace Academe\Elavon\Epg\Psr7\Contracts;

interface ValueObject
{
    /**
     * Creates an instance from JSON-compatible data.
     * @param mixed $data Can be array, string, int, bool, etc.
     */
    public static function fromData(mixed $data): static;

    /**
     * Converts to JSON-compatible data.
     * @return mixed Array, string, int, bool, etc.
     */
    public function toData(): mixed;
}
```

#### DataTransferObject Interface

Extended contract for complex DTOs with property type metadata:

```php
interface DataTransferObject extends ValueObject
{
    /**
     * Returns property type definitions for serialization.
     *
     * @return array<string, array<string>> Type => property names mapping
     */
    public static function getPropertyTypes(): array;

    /**
     * Returns shallow array of non-null properties (no recursion).
     */
    public function toObjectArray(): array;
}
```

### 2. Value Objects

Simple, immutable objects representing domain concepts. They:
- Have readonly properties
- Validate on construction
- Serialize to simple types (strings, arrays)
- May have domain methods (comparison, formatting)

**Example: Money Value Object**

```php
final class Money implements ValueObject
{
    public function __construct(
        public readonly string $amount,
        public readonly Currency $currency,
    ) {
        $this->validate();
    }

    public static function fromData(mixed $data): static
    {
        // Data is array: ['amount' => '99.99', 'currencyCode' => 'USD']
        return new self(
            amount: (string) $data['amount'],
            currency: Currency::from($data['currencyCode']),
        );
    }

    public function toData(): mixed
    {
        // Returns array matching API format
        return [
            'amount' => $this->amount,
            'currencyCode' => $this->currency->value,
        ];
    }

    // Domain methods
    public function isPositive(): bool { ... }
    public function equals(Money $other): bool { ... }
    public function negate(): self { ... }
}
```

**Example: EmailAddress Value Object (scalar-backed)**

```php
final class EmailAddress implements ValueObject
{
    public function __construct(
        public readonly string $value,
    ) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email: {$value}");
        }
    }

    public static function fromData(mixed $data): static
    {
        // Data is a simple string
        return new self((string) $data);
    }

    public function toData(): mixed
    {
        // Returns the string value
        return $this->value;
    }
}
```

### 3. Enums

PHP 8.1+ backed string enums for API-defined value sets:

```php
enum TransactionState: string
{
    case AUTHORIZATION_PENDING = 'authorizationPending';
    case AUTHORIZED = 'authorized';
    case DECLINED = 'declined';
    case CAPTURED = 'captured';
    case SETTLED = 'settled';
    case REFUNDED = 'refunded';
    case VOIDED = 'voided';
    case FAILED = 'failed';
    case UNKNOWN = 'unknown';
}

enum Currency: string
{
    case USD = 'USD';
    case EUR = 'EUR';
    case GBP = 'GBP';
    // ... all ISO 4217 codes
}
```

### 4. Data Transfer Objects (DTOs)

Complex objects representing API resources. They:
- Have many optional properties (different fields for request vs response)
- Accept both typed objects OR raw arrays in constructor
- Use the `SerializesData` trait for serialization
- Define `getPropertyTypes()` for type metadata

**Example: Transaction DTO (simplified)**

```php
class Transaction implements DataTransferObject
{
    use SerializesData;

    // Property type definitions for serialization
    public static function getPropertyTypes(): array
    {
        return [
            'object' => ['total', 'card', 'shipTo', 'billTo'],
            'array' => ['failures'],
            'enum' => ['state', 'type'],
            'string' => ['id', 'description', 'customReference', 'createdAt'],
            'boolean' => ['isAuthorized', 'isVoided', 'isCaptured'],
        ];
    }

    // Normalized properties (always typed)
    public readonly ?Money $total;
    public readonly ?Card $card;
    public readonly ?TransactionState $state;

    public function __construct(
        // Accept Money object OR array
        Money|array|null $total = null,
        Card|array|null $card = null,
        TransactionState|string|null $state = null,
        public readonly ?string $id = null,
        public readonly ?string $description = null,
        // ... more properties
    ) {
        // Normalize nested objects
        $this->total = match (true) {
            $total instanceof Money => $total,
            is_array($total) => Money::fromData($total),
            default => null,
        };

        $this->card = match (true) {
            $card instanceof Card => $card,
            is_array($card) => Card::fromData($card),
            default => null,
        };

        // Normalize enums
        $this->state = match (true) {
            $state instanceof TransactionState => $state,
            is_string($state) => TransactionState::from($state),
            default => null,
        };

        $this->validate();
    }
}
```

---

## Component Patterns

### SerializesData Trait

Provides reusable `fromData()`, `toData()`, and `toObjectArray()` implementations based on `getPropertyTypes()`:

```php
trait SerializesData
{
    public static function fromData(mixed $data): static
    {
        $propertyTypes = static::getPropertyTypes();
        $args = [];

        // Object/array properties - pass raw data to constructor
        foreach ($propertyTypes['object'] ?? [] as $prop) {
            $args[$prop] = $data[$prop] ?? null;
        }

        // Enum properties - pass raw string
        foreach ($propertyTypes['enum'] ?? [] as $prop) {
            $args[$prop] = $data[$prop] ?? null;
        }

        // String properties
        foreach ($propertyTypes['string'] ?? [] as $prop) {
            $args[$prop] = isset($data[$prop]) ? (string) $data[$prop] : null;
        }

        // Boolean properties
        foreach ($propertyTypes['boolean'] ?? [] as $prop) {
            $args[$prop] = isset($data[$prop]) ? (bool) $data[$prop] : null;
        }

        return new static(...$args);
    }

    public function toData(): mixed
    {
        $propertyTypes = static::getPropertyTypes();
        $data = [];

        // Convert objects recursively
        foreach ($propertyTypes['object'] ?? [] as $prop) {
            if ($this->$prop !== null) {
                $data[$prop] = $this->$prop->toData();
            }
        }

        // Convert enums to string values
        foreach ($propertyTypes['enum'] ?? [] as $prop) {
            if ($this->$prop !== null) {
                $data[$prop] = $this->$prop->value;
            }
        }

        // Add scalar properties
        foreach (['string', 'boolean', 'int'] as $type) {
            foreach ($propertyTypes[$type] ?? [] as $prop) {
                if ($this->$prop !== null) {
                    $data[$prop] = $this->$prop;
                }
            }
        }

        return $data;
    }
}
```

---

## HTTP Message Building

### Request Classes

Request classes build PSR-7 requests but don't add API-specific headers:

```php
class CreateTransactionRequest
{
    public function __construct(
        private readonly Transaction|array $transaction,
        private readonly ?array $requiredFields = null,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
        private readonly string $baseUri = 'https://api.example.com',
    ) {}

    public function build(): RequestInterface
    {
        $factory = $this->requestFactory ?? new Psr17Factory();
        $streamFactory = $this->streamFactory ?? new Psr17Factory();

        // Normalize to DTO
        $transaction = $this->transaction instanceof Transaction
            ? $this->transaction
            : Transaction::fromData($this->transaction);

        // Validate required fields
        $this->validateRequest($transaction);

        // Build PSR-7 request
        $body = json_encode($transaction->toData(), JSON_THROW_ON_ERROR);

        return $factory
            ->createRequest('POST', $this->baseUri . '/transactions')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withBody($streamFactory->createStream($body));
    }
}
```

### API Request Factory

The factory pattern applies API-specific headers to requests:

```php
class ElavonApiFactory
{
    public const REGION_EU = 'eu';
    public const REGION_US = 'us';
    public const ENV_PRODUCTION = 'production';
    public const ENV_UAT = 'uat';

    private ?string $region = null;
    private ?string $environment = null;
    private ?string $username = null;
    private ?string $password = null;

    public static function configure(): static
    {
        return new static();
    }

    public function apply(RequestInterface $request): RequestInterface
    {
        // Add Accept header
        if (!$request->hasHeader('Accept')) {
            $request = $request->withHeader('Accept', 'application/json;charset=UTF-8');
        }

        // Add Accept-Version header
        $request = $request->withHeader('Accept-Version', '1');

        // Add Content-Type for requests with body
        if (!$request->hasHeader('Content-Type') && $request->getBody()->getSize() > 0) {
            $request = $request->withHeader('Content-Type', 'application/json');
        }

        // Add authentication if configured
        if ($this->username !== null && $this->password !== null) {
            $request = $request->withHeader(
                'Authorization',
                'Basic ' . base64_encode("{$this->username}:{$this->password}")
            );
        }

        // Replace base URI if configured
        if ($this->region && $this->environment) {
            $request = $this->replaceBaseUri($request);
        }

        return $request;
    }

    public function withRegion(string $region): static { ... }
    public function withEnvironment(string $env): static { ... }
    public function withAuthentication(string $username, string $password): static { ... }
}
```

### Usage Example

```php
// Build base request
$request = new CreateTransactionRequest([
    'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
    'card' => [
        'number' => '4111111111111111',
        'expirationMonth' => 12,
        'expirationYear' => 2025,
    ],
]);

// Configure factory with API settings
$factory = ElavonApiFactory::configure()
    ->withRegion('eu')
    ->withEnvironment('sandbox')
    ->withAuthentication($merchantAlias, $apiSecret);

// Apply factory to request and send
$apiRequest = $factory->apply($request->build());
$response = $httpClient->sendRequest($apiRequest);

// Parse response
$transactionResponse = TransactionResponse::fromPsr7Response($response);

if ($transactionResponse->hasError()) {
    $error = $transactionResponse->getError();
    echo "Error: {$error->getMessage()}";
} else {
    $transaction = $transactionResponse->getTransaction();
    echo "Transaction ID: {$transaction->id}";
}
```

---

## Response Classes

Response classes parse PSR-7 responses into typed objects:

```php
class TransactionResponse
{
    use HandlesErrors;

    private readonly ?Transaction $transaction;

    public function __construct(
        private readonly ResponseInterface $response,
    ) {
        if ($this->isSuccessful()) {
            $this->transaction = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->transaction = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    public static function fromPsr7Response(ResponseInterface $response): self
    {
        return new self($response);
    }

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    private function parseSuccessResponse(): Transaction
    {
        $data = $this->parseJsonBody();
        return Transaction::fromData($data);
    }
}
```

### HandlesErrors Trait

Shared error handling for all response classes:

```php
trait HandlesErrors
{
    private readonly ?ErrorResponse $error;

    public function hasError(): bool
    {
        return $this->error !== null;
    }

    public function getError(): ?ErrorResponse
    {
        return $this->error;
    }

    public function isSuccessful(): bool
    {
        $statusCode = $this->getStatusCode();
        return $statusCode >= 200 && $statusCode < 300;
    }

    private function parseErrorResponse(): ErrorResponse
    {
        $data = $this->parseJsonBody();
        return ErrorResponse::fromData($data);
    }

    abstract private function parseJsonBody(): array;
    abstract public function getStatusCode(): int;
}
```

---

## Error Handling

### ErrorResponse DTO

```php
class ErrorResponse implements DataTransferObject
{
    use SerializesData;

    public static function getPropertyTypes(): array
    {
        return [
            'int' => ['status'],
            'string' => ['message'],
            'array' => ['failures'],
        ];
    }

    /** @var array<Failure>|null */
    public readonly ?array $failures;

    public function __construct(
        public readonly ?int $status = null,
        public readonly ?string $message = null,
        ?array $failures = null,
    ) {
        $this->failures = $failures !== null
            ? array_map(fn($f) => $f instanceof Failure ? $f : Failure::fromData($f), $failures)
            : null;
    }

    public function getCode(): ?string
    {
        return $this->failures[0]->code ?? null;
    }

    public function getMessage(): string
    {
        return $this->failures[0]->description
            ?? $this->message
            ?? 'Unknown error';
    }
}
```

### Exception Hierarchy

```
Exception
└── EpgException (marker interface)
    ├── InvalidArgumentException  # Validation errors
    ├── ValidationException       # Schema validation errors
    └── SerializationException    # JSON parse errors
```

---

## Serialization Strategy

### Key Rules

1. **`fromData()`**: Creates object from JSON-compatible data (arrays, strings, etc.)
2. **`toData()`**: Converts object to JSON-compatible data (for request bodies)
3. **Normalization in Constructor**: DTOs accept union types and normalize in constructor
4. **Null Handling**: Only non-null properties are serialized to JSON

### Data Flow

```
API Request:
  PHP Objects → toData() → JSON → HTTP Request Body

API Response:
  HTTP Response Body → JSON → fromData() → PHP Objects
```

### Type Normalization Pattern

Constructors accept flexible input and normalize to strongly-typed properties:

```php
public function __construct(
    Money|array|null $total = null,           // Accept Money OR array
    TransactionState|string|null $state = null, // Accept enum OR string
) {
    // Normalize to Money object
    $this->total = match (true) {
        $total instanceof Money => $total,
        is_array($total) => Money::fromData($total),
        default => null,
    };

    // Normalize to enum
    $this->state = match (true) {
        $state instanceof TransactionState => $state,
        is_string($state) => TransactionState::tryFrom($state),
        default => null,
    };
}
```

---

## Testing Strategy

### Unit Tests

Test DTOs, value objects, and enums in isolation:

```php
class TransactionTest extends TestCase
{
    public function test_fromData_parsesApiResponse(): void
    {
        $data = [
            'id' => 'txn_123',
            'state' => 'authorized',
            'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
        ];

        $transaction = Transaction::fromData($data);

        $this->assertSame('txn_123', $transaction->id);
        $this->assertSame(TransactionState::AUTHORIZED, $transaction->state);
        $this->assertSame('99.99', $transaction->total->amount);
    }

    public function test_toData_serializesForRequest(): void
    {
        $transaction = new Transaction(
            total: new Money('99.99', Currency::USD),
            description: 'Test payment',
        );

        $data = $transaction->toData();

        $this->assertSame('99.99', $data['total']['amount']);
        $this->assertSame('USD', $data['total']['currencyCode']);
        $this->assertSame('Test payment', $data['description']);
    }
}
```

### Integration Tests

Test against real API (sandbox environment):

```php
class IntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        $this->loadEnv();

        $this->merchantAlias = getenv('API_MERCHANT_ALIAS') ?: '';
        $this->apiSecret = getenv('API_SECRET') ?: '';

        if (empty($this->merchantAlias) || empty($this->apiSecret)) {
            $this->markTestSkipped('Credentials not configured');
        }

        $this->httpClient = new Client(['timeout' => 30]);
    }

    public function test_createTransaction_returnsAuthorized(): void
    {
        $request = new CreateTransactionRequest([
            'total' => ['amount' => '10.00', 'currencyCode' => 'USD'],
            'card' => ['number' => '4111111111111111', ...],
        ]);

        $factory = ElavonApiFactory::configure()
            ->withRegion('eu')
            ->withEnvironment('sandbox')
            ->withAuthentication($this->merchantAlias, $this->apiSecret);

        $apiRequest = $factory->apply($request->build());
        $response = $this->httpClient->sendRequest($apiRequest);
        $result = TransactionResponse::fromPsr7Response($response);

        $this->assertFalse($result->hasError());
        $this->assertNotNull($result->getTransaction());
        $this->assertSame(TransactionState::AUTHORIZED, $result->getTransaction()->state);
    }
}
```

---

## Creating a New API Package

### Step-by-Step Guide

#### 1. Analyze the API

From OpenAPI spec or documentation, identify:
- **Resources**: Transaction, Customer, Order, etc.
- **Operations**: Create, Retrieve, Update, Delete, List
- **Endpoints**: POST /transactions, GET /transactions/{id}, etc.
- **Authentication**: Basic Auth, Bearer Token, API Key headers
- **Common Structures**: Money, Address, Pagination

#### 2. Create Directory Structure

```bash
mkdir -p src/{Contracts,Concerns,Dtos,Enums,Exceptions,Messages/{Request,Response},Support,ValueObjects}
mkdir -p tests/{Unit,Integration,Fixtures}
```

#### 3. Define Contracts

Create `ValueObject` and `DataTransferObject` interfaces.

#### 4. Create Enums

One enum per API-defined value set:

```php
// From OpenAPI: enum: ["pending", "active", "cancelled"]
enum SubscriptionState: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case CANCELLED = 'cancelled';
}
```

#### 5. Create Value Objects

For validated, immutable domain concepts:

```php
// Money, Currency, EmailAddress, PhoneNumber, etc.
```

#### 6. Create DTOs

One DTO per API resource/schema:

```php
// From OpenAPI schema definitions
class Transaction implements DataTransferObject { ... }
class Customer implements DataTransferObject { ... }
```

#### 7. Create Request Classes

One request class per API operation:

```php
// POST /transactions → CreateTransactionRequest
// GET /transactions/{id} → RetrieveTransactionRequest
// PATCH /transactions/{id} → UpdateTransactionRequest
// GET /transactions → RetrieveTransactionListRequest
```

#### 8. Create Response Classes

One response class per unique response structure:

```php
// Single resource → TransactionResponse
// List of resources → TransactionListResponse
// Error response → ErrorResponse (shared)
```

#### 9. Create API Request Factory

Configure authentication, base URI, and API-specific headers:

```php
class MyApiFactory
{
    public function withAuthentication(string $apiKey): static { ... }
    public function withRegion(string $region): static { ... }
    public function withEnvironment(string $env): static { ... }
    public function apply(RequestInterface $request): RequestInterface { ... }
}
```

#### 10. Write Tests

- Unit tests for all DTOs and value objects
- Integration tests for key operations
- Use JSON fixtures from API documentation

---

## Checklist for New API Package

- [ ] Composer package with PSR-4 autoloading
- [ ] PHP 8.1+ requirement
- [ ] PSR-7/PSR-17 dependencies only
- [ ] `ValueObject` and `DataTransferObject` interfaces
- [ ] `SerializesData` trait
- [ ] All API enums defined
- [ ] Value objects for validated types (Money, Email, etc.)
- [ ] DTOs for all API schemas
- [ ] Request classes for all API operations
- [ ] Response classes with error handling
- [ ] API request factory for auth/headers
- [ ] Built-in PSR-7/PSR-17 implementations (optional)
- [ ] Unit tests for serialization roundtrips
- [ ] Integration tests against sandbox
- [ ] PHPStan level 8 compliance
- [ ] PSR-12 coding style
- [ ] README with usage examples
