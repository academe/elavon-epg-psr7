# Creating Response Classes

This guide explains how to create new response classes for the Elavon Payment Gateway API.

## Using the HandlesErrors Trait

All response classes should use the `HandlesErrors` trait to provide consistent error handling:

```php
<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response;

use Academe\Elavon\Epg\Psr7\DataObjects\YourDataObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

class YourResponse
{
    use HandlesErrors;

    private readonly ?YourDataObject $data;

    public function __construct(
        private readonly ResponseInterface $response,
    ) {
        // Parse based on status code
        if ($this->isSuccessful()) {
            $this->data = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->data = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    public function getData(): ?YourDataObject
    {
        return $this->data;
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    private function parseSuccessResponse(): YourDataObject
    {
        $data = $this->parseJsonBody();
        return YourDataObject::fromArray($data);
    }

    private function parseJsonBody(): array
    {
        $body = (string) $this->response->getBody();

        if ($body === '') {
            throw new InvalidArgumentException('Response body is empty');
        }

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new InvalidArgumentException(
                'Failed to decode JSON response: ' . $e->getMessage(),
                previous: $e
            );
        }

        if (!is_array($data)) {
            throw new InvalidArgumentException('Response body is not a JSON object');
        }

        return $data;
    }
}
```

## What the Trait Provides

The `HandlesErrors` trait provides:

### Properties

- `private readonly ?ErrorResponse $error` - Stores error details

### Methods

- `hasError(): bool` - Check if response has an error
- `getError(): ?ErrorResponse` - Get error details
- `isSuccessful(): bool` - Check if response is 2xx

### Requirements

Your class must implement:

- `getStatusCode(): int` - Return the HTTP status code
- `parseJsonBody(): array` - Parse and return the response body as an array

The trait provides `parseErrorResponse()` which calls `parseJsonBody()`.

## Example: TransactionResponse

Here's how `TransactionResponse` uses the trait:

```php
class TransactionResponse
{
    use HandlesErrors;

    private readonly ?Transaction $transaction;

    public function __construct(
        private readonly ResponseInterface $response,
    ) {
        // Parse response based on status code
        if ($this->isSuccessful()) {
            $this->transaction = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->transaction = null;
            $this->error = $this->parseErrorResponse(); // From trait
        }
    }

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    private function parseSuccessResponse(): Transaction
    {
        $data = $this->parseJsonBody();
        return Transaction::fromArray($data);
    }

    private function parseJsonBody(): array
    {
        // Parse JSON response
        // ...
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
    $error = $response->getError();
    echo "Error: {$error->getMessage()}\n";
} else {
    $data = $response->getData();
    // Process successful response
}
```

## Error Response Structure

All error responses are parsed into `ErrorResponse` objects:

```php
class ErrorResponse
{
    public int $status;
    public array $failures; // ErrorDetail[]

    public function getMessage(): string;
    public function getCode(): string;
    public function getFailures(): array;
    public function hasErrorCode(string $code): bool;
}
```

See [Error Handling](error-handling.md) for complete error handling documentation.

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
    $response = new YourResponse($psr7Response);

    // Assert
    $this->assertTrue($response->hasError());
    $this->assertFalse($response->isSuccessful());
    $this->assertNull($response->getData());

    $error = $response->getError();
    $this->assertNotNull($error);
    $this->assertSame('unauthorized', $error->getCode());
}
```

## See Also

- [Error Handling Guide](error-handling.md) - Complete error handling documentation
- [HandlesErrors Trait](../src/Messages/Response/Concerns/HandlesErrors.php) - Trait source code
- [TransactionResponse](../src/Messages/Response/TransactionResponse.php) - Example implementation
