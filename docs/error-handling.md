# Error Handling

This document explains how to handle errors when using the Elavon Payment Gateway PSR-7 library.

## Overview

The library handles two types of responses from the API:

1. **Success Responses** (HTTP 2xx) - Contain transaction data
2. **Error Responses** (HTTP 4xx, 5xx) - Contain error details

The `TransactionResponse` class automatically parses both types of responses based on the HTTP status code.

## Checking for Errors

Always check for errors before accessing transaction data:

```php
use Academe\Elavon\Epg\Psr7\Messages\Request\Transaction\CreateTransactionRequest;
use Academe\Elavon\Epg\Psr7\Messages\Response\Transaction\TransactionResponse;

// Build and send request
$request = new CreateTransactionRequest(...);
$psr7Request = $request->build();
$psr7Response = $httpClient->sendRequest($psr7Request);

// Parse response
$response = TransactionResponse::fromPsr7Response($psr7Response);

// Check for errors
if ($response->hasError()) {
    // Handle error
    $error = $response->getError();

    echo "Error: " . $error->getMessage() . "\n";
    echo "Error Code: " . $error->getCode() . "\n";
    echo "HTTP Status: " . $error->status . "\n";

    // Handle specific error codes
    if ($error->hasErrorCode('unauthorized')) {
        // Invalid API credentials
    } elseif ($error->hasErrorCode('validation_error')) {
        // Invalid request data
    }
} else {
    // Success - access transaction data
    $transaction = $response->getTransaction();
    echo "Transaction ID: " . $transaction->id . "\n";
}
```

## Error Response Structure

Error responses from the API follow this structure:

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

This is parsed into an `ErrorResponse` object with an array of `ErrorDetail` objects.

## Common Error Codes

### Authentication Errors (401)

**Code**: `unauthorized`

**Description**: Invalid API credentials

**Solution**: Verify your merchant alias and API key

```php
if ($response->hasError() && $response->getError()->hasErrorCode('unauthorized')) {
    throw new \RuntimeException('Invalid API credentials');
}
```

### Validation Errors (400)

**Code**: `validation_error`

**Description**: Invalid request data

**Field**: Indicates which field failed validation

```php
if ($response->hasError()) {
    $error = $response->getError();

    foreach ($error->failures as $failure) {
        if ($failure->code === 'validation_error') {
            echo "Validation error on field '{$failure->field}': {$failure->description}\n";
        }
    }
}
```

### Server Errors (500)

**Code**: `internal_error`

**Description**: Server-side error

**Solution**: Retry the request or contact support

## ErrorResponse API

The `ErrorResponse` class provides several helper methods:

```php
$error = $response->getError();

// Get primary error message (from first failure)
$message = $error->getMessage();

// Get primary error code (from first failure)
$code = $error->getCode();

// Get HTTP status code
$status = $error->status;

// Check for specific error code
if ($error->hasErrorCode('unauthorized')) {
    // Handle unauthorized error
}

// Access all failures (using getter - recommended)
foreach ($error->getFailures() as $failure) {
    echo "{$failure->code}: {$failure->description}";

    if ($failure->field !== null) {
        echo " (field: {$failure->field})";
    }

    echo "\n";
}

// Or access directly via property (also works)
foreach ($error->failures as $failure) {
    // ...
}

// Convert to array
$errorArray = $error->toArray();
```

## ErrorDetail API

Each `ErrorDetail` represents a single error:

```php
$failure = $error->failures[0];

// Error code (e.g., "unauthorized", "validation_error")
$code = $failure->code;

// Human-readable description
$description = $failure->description;

// Field that caused the error (null for general errors)
$field = $failure->field;

// Convert to array
$failureArray = $failure->toArray();
```

## Complete Example

```php
<?php

use Academe\Elavon\Epg\Psr7\Messages\Request\Transaction\CreateTransactionRequest;
use Academe\Elavon\Epg\Psr7\Messages\Response\Transaction\TransactionResponse;
use GuzzleHttp\Client;

// Create HTTP client
$httpClient = new Client([
    'base_uri' => 'https://uat.api.converge.eu.elavonaws.com',
    'auth' => [$merchantAlias, $apiKey],
    'http_errors' => false, // Don't throw on 4xx/5xx
]);

// Create transaction request
$request = new CreateTransactionRequest(
    transaction: [
        'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
        'card' => [
            'number' => '4111111111111111',
            'securityCode' => '123',
            'expirationMonth' => 12,
            'expirationYear' => 2025,
        ],
    ],
    baseUri: 'https://uat.api.converge.eu.elavonaws.com',
);

// Send request
$psr7Request = $request->build();
$psr7Response = $httpClient->send($psr7Request);

// Parse response
$response = TransactionResponse::fromPsr7Response($psr7Response);

// Handle response
if ($response->hasError()) {
    // Error response
    $error = $response->getError();

    // Log error details
    error_log(sprintf(
        'API Error (HTTP %d): %s [Code: %s]',
        $error->status,
        $error->getMessage(),
        $error->getCode()
    ));

    // Handle specific errors
    if ($error->hasErrorCode('unauthorized')) {
        throw new \RuntimeException('Authentication failed. Check your API credentials.');
    }

    if ($error->hasErrorCode('validation_error')) {
        $messages = [];
        foreach ($error->getFailures() as $failure) {
            $messages[] = $failure->field
                ? "{$failure->field}: {$failure->description}"
                : $failure->description;
        }

        throw new \InvalidArgumentException('Validation failed: ' . implode(', ', $messages));
    }

    // Generic error
    throw new \RuntimeException($error->getMessage());

} else {
    // Success response
    $transaction = $response->getTransaction();

    echo "Success!\n";
    echo "Transaction ID: {$transaction->id}\n";
    echo "State: {$transaction->state->value}\n";
    echo "Amount: {$transaction->total->amount} {$transaction->total->currency->value}\n";
}
```

## Testing Error Handling

When testing, you can simulate error responses:

```php
use Academe\Elavon\Epg\Psr7\Messages\Response\Transaction\TransactionResponse;

// Create a mock error response
$errorBody = json_encode([
    'status' => 401,
    'failures' => [
        [
            'code' => 'unauthorized',
            'description' => 'Invalid API key',
            'field' => null,
        ],
    ],
]);

$mockResponse = $this->createMockResponse($errorBody, 401);
$response = TransactionResponse::fromPsr7Response($mockResponse);

// Test error handling
$this->assertTrue($response->hasError());
$this->assertSame('unauthorized', $response->getError()->getCode());
```

## Best Practices

1. **Always check for errors** before accessing transaction data
2. **Don't throw exceptions immediately** - log the error first for debugging
3. **Handle specific error codes** to provide better user feedback
4. **Check field-specific errors** for validation failures
5. **Implement retry logic** for transient errors (500, 503)
6. **Never expose raw error messages** to end users - translate them first
7. **Log full error details** including HTTP status and all failures

## See Also

- [Integration Tests README](../tests/Integration/README.md) - Testing against real API
- [Transaction Response API](../src/Messages/Response/TransactionResponse.php)
- [Error Response DTO](../src/DataObjects/ErrorResponse.php)
- [Error Detail DTO](../src/DataObjects/ErrorDetail.php)
