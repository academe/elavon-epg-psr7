# Error Handling Update

## Problem Statement

The integration tests were failing because the `TransactionResponse` class was trying to parse error responses (like 401 Unauthorized) as if they were successful transaction responses. This caused validation errors because error responses have a completely different structure than success responses.

### Example Error Response
```json
{
  "status": 401,
  "failures": [
    {
      "code": "unauthorized",
      "description": "A valid API key is required",
      "field": null
    }
  ]
}
```

This was trying to be parsed as a `Transaction` object, which expected a `total` field, causing the error:
```
Failed asserting that exception message 'Missing required field: total' contains 'Response body is not a JSON object'.
```

## Solution

Implemented proper error handling by making `TransactionResponse` capable of handling both success and error responses:

1. **Created ErrorDetail DTO** - Represents individual error/failure details
2. **Created ErrorResponse DTO** - Represents complete error responses with helper methods
3. **Updated TransactionResponse** - Now parses responses based on HTTP status code:
   - 2xx status codes → Parse as `Transaction`
   - 4xx/5xx status codes → Parse as `ErrorResponse`

## Files Created

### 1. Error DTOs

**[src/DataObjects/ErrorDetail.php](src/DataObjects/ErrorDetail.php)**
```php
readonly class ErrorDetail
{
    public function __construct(
        public string $code,
        public string $description,
        public ?string $field = null,
    ) {}
}
```

**[src/DataObjects/ErrorResponse.php](src/DataObjects/ErrorResponse.php)**
```php
readonly class ErrorResponse
{
    public function __construct(
        public int $status,
        public array $failures = [],
    ) {}

    public function getMessage(): string { ... }
    public function getCode(): string { ... }
    public function hasErrorCode(string $code): bool { ... }
}
```

### 2. Documentation

**[docs/error-handling.md](docs/error-handling.md)** - Complete error handling guide with examples

### 3. Unit Tests

- **[tests/Unit/DataObjects/ErrorDetailTest.php](tests/Unit/DataObjects/ErrorDetailTest.php)** - 6 tests
- **[tests/Unit/DataObjects/ErrorResponseTest.php](tests/Unit/DataObjects/ErrorResponseTest.php)** - 13 tests
- Updated **[tests/Unit/Messages/Response/TransactionResponseTest.php](tests/Unit/Messages/Response/TransactionResponseTest.php)** - Added 4 error tests

## Files Modified

### 1. TransactionResponse

**[src/Messages/Response/TransactionResponse.php](src/Messages/Response/TransactionResponse.php)**

**Before:**
```php
class TransactionResponse
{
    private readonly Transaction $transaction;

    public function __construct(ResponseInterface $response)
    {
        $this->transaction = $this->parseResponse();
    }

    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }
}
```

**After:**
```php
class TransactionResponse
{
    private readonly ?Transaction $transaction;
    private readonly ?ErrorResponse $error;

    public function __construct(ResponseInterface $response)
    {
        if ($this->isSuccessful()) {
            $this->transaction = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->transaction = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    public function getTransaction(): ?Transaction { ... }
    public function getError(): ?ErrorResponse { ... }
    public function hasError(): bool { ... }
}
```

### 2. Integration Tests

**[tests/Integration/OpayoIntegrationTest.php](tests/Integration/OpayoIntegrationTest.php)**

Added error checking before accessing transaction data:

```php
// Check for errors first
if ($response->hasError()) {
    $error = $response->getError();
    $this->fail(
        sprintf(
            "API returned error (HTTP %d): %s\nError code: %s\nFull response: %s",
            $response->getStatusCode(),
            $error->getMessage(),
            $error->getCode(),
            (string) $psr7Response->getBody()
        )
    );
}

$transaction = $response->getTransaction();
$this->assertNotNull($transaction);
```

## API Changes

### New Methods on TransactionResponse

```php
// Check if response has an error
public function hasError(): bool

// Get error details (null if successful)
public function getError(): ?ErrorResponse

// Get transaction (null if error) - CHANGED FROM Transaction to ?Transaction
public function getTransaction(): ?Transaction
```

### New ErrorResponse Methods

```php
// Get primary error message
public function getMessage(): string

// Get primary error code
public function getCode(): string

// Check for specific error code
public function hasError Code(string $code): bool

// Access all failures
public array $failures

// HTTP status code
public int $status
```

## Usage Example

```php
$response = TransactionResponse::fromPsr7Response($psr7Response);

// Always check for errors first
if ($response->hasError()) {
    $error = $response->getError();

    echo "Error: " . $error->getMessage() . "\n";
    echo "Code: " . $error->getCode() . "\n";
    echo "Status: " . $error->status . "\n";

    // Handle specific errors
    if ($error->hasErrorCode('unauthorized')) {
        throw new AuthException('Invalid credentials');
    }

    if ($error->hasErrorCode('validation_error')) {
        foreach ($error->failures as $failure) {
            echo "Field '{$failure->field}': {$failure->description}\n";
        }
    }

} else {
    // Success - transaction is never null here
    $transaction = $response->getTransaction();
    echo "Transaction ID: {$transaction->id}\n";
}
```

## Test Results

### Before Changes
```
Tests: 197, Assertions: 444, Failures: 2
- TransactionResponseTest::test_construct_withJsonArray_throwsException (FAILED)
- Psr17FactoryTest::test_createStreamFromFile_withWriteMode_returnsWritableStream (FAILED - pre-existing)
```

### After Changes
```
Tests: 220, Assertions: 504, Failures: 1
- Psr17FactoryTest::test_createStreamFromFile_withWriteMode_returnsWritableStream (FAILED - pre-existing)

New tests added: 23
- ErrorDetailTest: 6 tests
- ErrorResponseTest: 13 tests
- TransactionResponseTest: 4 new error handling tests
```

## Breaking Changes

### getTransaction() Return Type

**Before:** `public function getTransaction(): Transaction`

**After:** `public function getTransaction(): ?Transaction`

**Impact:** Code that calls `getTransaction()` may need to handle null returns or check `hasError()` first.

**Migration:**
```php
// Old code (will still work, but may get null)
$transaction = $response->getTransaction();

// New code (recommended)
if ($response->hasError()) {
    // Handle error
    $error = $response->getError();
} else {
    // Success - transaction is not null
    $transaction = $response->getTransaction();
}
```

## Benefits

1. ✅ **Proper error handling** - No more exceptions when API returns errors
2. ✅ **Type-safe** - Error and success responses are distinct types
3. ✅ **Helpful error info** - Access to error codes, messages, and affected fields
4. ✅ **Easy error detection** - Simple `hasError()` check
5. ✅ **Comprehensive tests** - 23 new unit tests covering all error scenarios
6. ✅ **Well documented** - Complete error handling guide

## Next Steps

Integration tests will now properly show authentication errors and other API errors instead of confusing validation errors. Users can:

1. Set up credentials in `.env` file
2. Run `composer test:integration`
3. Get clear error messages if credentials are invalid or other issues occur

## See Also

- [Error Handling Documentation](docs/error-handling.md)
- [Integration Tests Setup](INTEGRATION_TESTS.md)
- [API Changes Summary](CHANGES.md)
