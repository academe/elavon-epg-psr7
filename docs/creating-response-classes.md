# Creating Response Classes

This guide explains how to create new response classes for the Elavon Payment Gateway API.

## Using the ParsesPsr7Response Trait

All response classes should use the `ParsesPsr7Response` trait to provide consistent error handling:

```php
<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\YourResource;

use Academe\Elavon\Epg\Psr7\Contracts\ResponseMessage;
use Academe\Elavon\Epg\Psr7\Dtos\YourDataObject;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

class YourResponse implements ResponseMessage
{
    use ParsesPsr7Response;

    public readonly ?YourDataObject $data;

    /**
     * @param array<string, mixed> $data Parsed response body data
     * @param int $statusCode HTTP status code
     */
    public function __construct(array $data, int $statusCode)
    {
        $this->statusCode = $statusCode;

        // Parse based on status code
        if ($this->isSuccessful()) {
            $this->data = YourDataObject::fromData($data);
            $this->error = null;
        } else {
            $this->data = null;
            $this->error = self::parseErrorData($data);
        }
    }
}
```

## What the Trait Provides

The `ParsesPsr7Response` trait provides:

### Properties

- `public readonly int $statusCode` - HTTP status code
- `public readonly ?ErrorResponse $error` - Stores error details

### Methods

- `fromPsr7Response(ResponseInterface): static` - Factory to create from PSR-7 response
- `isSuccessful(): bool` - Check if response is 2xx
- `hasError(): bool` - Check if response has an error

### Protected Methods

- `parseErrorData(array): ErrorResponse` - Parse error data into ErrorResponse

## Example: TransactionResponse

Here's how `TransactionResponse` uses the trait:

```php
<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\Transaction;

use Academe\Elavon\Epg\Psr7\Contracts\ResponseMessage;
use Academe\Elavon\Epg\Psr7\Dtos\Transaction;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

class TransactionResponse implements ResponseMessage
{
    use ParsesPsr7Response;

    public readonly ?Transaction $transaction;

    /**
     * @param array<string, mixed> $data Parsed response body data
     * @param int $statusCode HTTP status code
     */
    public function __construct(array $data, int $statusCode)
    {
        $this->statusCode = $statusCode;

        // Parse response based on status code
        if ($this->isSuccessful()) {
            $this->transaction = Transaction::fromData($data);
            $this->error = null;
        } else {
            $this->transaction = null;
            $this->error = self::parseErrorData($data);
        }
    }
}
```

## Benefits

Using the trait provides:

1. **Consistency** - All response classes handle errors the same way
2. **Reusability** - Error handling logic is written once
3. **Type Safety** - `ErrorResponse` DTO for all errors
4. **Easy Testing** - Mock responses can easily check error handling
5. **Future-Proof** - Adding new response types is simple

## Usage Pattern

```php
$response = YourResponse::fromPsr7Response($psr7Response);

if ($response->hasError()) {
    $error = $response->error;
    echo "Error: {$error->getMessage()}\n";
} else {
    $data = $response->data;
    // Process successful response
}
```

## Error Response Structure

All error responses are parsed into `ErrorResponse` objects:

```php
class ErrorResponse
{
    public readonly ?int $status;
    public readonly ?array $failures; // ErrorDetail[]

    public function getMessage(): string;
    public function getCode(): ?string;
    public function getFailures(): array;
}
```

## Testing Response Classes

Example test for a response class using the trait:

```php
public function test_construct_withError_parsesError(): void
{
    // Arrange
    $responseBody = json_encode([
        'status' => 401,
        'failures' => [
            [
                'code' => 'unauthorized',
                'description' => 'Invalid API key',
                'field' => null,
            ],
        ],
    ]);

    $psr7Response = $this->createMockResponse($responseBody, 401);

    // Act
    $response = YourResponse::fromPsr7Response($psr7Response);

    // Assert
    $this->assertTrue($response->hasError());
    $this->assertFalse($response->isSuccessful());
    $this->assertNull($response->data);

    $error = $response->error;
    $this->assertNotNull($error);
    $this->assertSame('unauthorized', $error->getCode());
}
```

## See Also

- [ParsesPsr7Response Trait](../src/Messages/Response/Concerns/ParsesPsr7Response.php) - Trait source code
- [TransactionResponse](../src/Messages/Response/Transaction/TransactionResponse.php) - Example implementation
