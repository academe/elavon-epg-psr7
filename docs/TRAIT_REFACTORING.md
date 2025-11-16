# HandlesErrors Trait Refactoring

## Summary

Extracted error handling logic from `TransactionResponse` into a reusable `HandlesErrors` trait. This allows any response class to easily support error handling with consistent behavior.

## Changes Made

### 1. Created HandlesErrors Trait

**[src/Messages/Response/Concerns/HandlesErrors.php](src/Messages/Response/Concerns/HandlesErrors.php)**

```php
trait HandlesErrors
{
    private readonly ?ErrorResponse $error;

    public function hasError(): bool;
    public function getError(): ?ErrorResponse;
    public function isSuccessful(): bool;
    private function parseErrorResponse(): ErrorResponse;

    // Requires implementation:
    abstract private function parseJsonBody(): array;
    abstract public function getStatusCode(): int;
}
```

### 2. Refactored TransactionResponse

**Before:**
```php
class TransactionResponse
{
    private readonly ?Transaction $transaction;
    private readonly ?ErrorResponse $error;

    public function hasError(): bool { ... }
    public function getError(): ?ErrorResponse { ... }
    public function isSuccessful(): bool { ... }
    private function parseErrorResponse(): ErrorResponse { ... }
}
```

**After:**
```php
class TransactionResponse
{
    use HandlesErrors;

    private readonly ?Transaction $transaction;

    // hasError(), getError(), isSuccessful(), parseErrorResponse()
    // are now provided by the trait
}
```

### 3. Added Tests

**[tests/Unit/Messages/Response/Concerns/HandlesErrorsTest.php](tests/Unit/Messages/Response/Concerns/HandlesErrorsTest.php)**

- 6 new tests for the trait
- Tests error detection, success checking, and status code handling

### 4. Added Documentation

**[docs/creating-response-classes.md](docs/creating-response-classes.md)**

Complete guide for creating new response classes using the trait.

## Benefits

### 1. Reusability

Any new response class can use error handling by adding one line:

```php
class CaptureResponse
{
    use HandlesErrors;
    // ... rest of implementation
}
```

### 2. Consistency

All response classes handle errors the same way:

```php
if ($response->hasError()) {
    $error = $response->getError();
    // Same API for all response types
}
```

### 3. Maintainability

Error handling logic is in one place. Bug fixes and improvements automatically apply to all response classes.

### 4. Testability

The trait can be tested independently, and response classes inherit this tested behavior.

### 5. Extensibility

Easy to add new response types (Capture, Void, Refund, etc.) without duplicating error handling code.

## Usage Example

Creating a new response class:

```php
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;

class CaptureResponse
{
    use HandlesErrors;

    private readonly ?Capture $capture;

    public function __construct(
        private readonly ResponseInterface $response,
    ) {
        if ($this->isSuccessful()) {
            $this->capture = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->capture = null;
            $this->error = $this->parseErrorResponse(); // From trait
        }
    }

    public function getCapture(): ?Capture
    {
        return $this->capture;
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    private function parseSuccessResponse(): Capture
    {
        $data = $this->parseJsonBody();
        return Capture::fromArray($data);
    }

    private function parseJsonBody(): array
    {
        // Parse JSON
    }
}
```

Usage is identical across all response types:

```php
$captureResponse = CaptureResponse::fromPsr7Response($psr7Response);
$transactionResponse = TransactionResponse::fromPsr7Response($psr7Response);

// Same error handling API
foreach ([$captureResponse, $transactionResponse] as $response) {
    if ($response->hasError()) {
        $error = $response->getError();
        echo $error->getMessage();
    }
}
```

## Trait API

### Provided Properties

```php
private readonly ?ErrorResponse $error
```

### Provided Methods

```php
// Check if response has an error
public function hasError(): bool

// Get error details (null if successful)
public function getError(): ?ErrorResponse

// Check if response was successful (2xx)
public function isSuccessful(): bool

// Parse error response (protected, for internal use)
private function parseErrorResponse(): ErrorResponse
```

### Required Methods

Classes using the trait must implement:

```php
// Return HTTP status code
public function getStatusCode(): int

// Parse JSON response body
private function parseJsonBody(): array
```

## Test Results

**Before trait:**
- Tests: 221
- Assertions: 510

**After trait:**
- Tests: 227 (+6 trait tests)
- Assertions: 520 (+10)
- All tests pass ✅

## Future Response Classes

Future response types can easily use the trait:

- `CaptureResponse` - Capture transactions
- `VoidResponse` - Void transactions
- `RefundResponse` - Refund transactions
- `TokenResponse` - Tokenization responses
- `BatchResponse` - Batch operations
- `AccountResponse` - Account/merchant info
- `ReportResponse` - Reporting endpoints

All will automatically have consistent error handling.

## Migration Notes

No breaking changes for existing code. The `TransactionResponse` API remains identical:

```php
// This still works exactly the same
$response = TransactionResponse::fromPsr7Response($psr7Response);

if ($response->hasError()) {
    $error = $response->getError();
    // ...
}
```

The refactoring is purely internal - extracting duplicate code into a reusable trait.

## See Also

- [Creating Response Classes Guide](docs/creating-response-classes.md)
- [Error Handling Guide](docs/error-handling.md)
- [HandlesErrors Trait Source](src/Messages/Response/Concerns/HandlesErrors.php)
- [TransactionResponse Example](src/Messages/Response/TransactionResponse.php)
