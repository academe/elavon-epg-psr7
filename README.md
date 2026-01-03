# Elavon EPG PSR-7

PSR-7 HTTP messages and Data Transfer Object (DTO) classes for the Elavon Payment Gateway (EPG) API.

The [API is described here](https://developer.elavon.com/products/en-uk/elavon-payment-gateway/v1/api-reference) and the [OpenAPI description is here](./docs/openapi.json)

## Overview

This package provides strongly-typed PHP classes for interacting with the Elavon Payment Gateway API. It includes:

- PSR-7 compliant HTTP message implementations
- DTO classes for request and response data structures
- Type-safe value objects for payment data
- Support for all EPG API resources and operations

This package handles message construction and serialization. A separate HTTP client package will handle the actual sending of requests.

## Get an Elavon Merchant Account

> [!TIP]
> Don't have an Elavon merchant account yet? [**Sign up here**](https://www.elavon.eu/partner-form.html?partner_id=0014H00004E0iYw) to get started. Elavon's team will contact you within 24-48 hours to discuss your payment processing needs.

## Requirements

- PHP 8.1 or higher
- An [Elavon merchant account](https://www.elavon.eu/partner-form.html?partner_id=0014H00004E0iYw) with API credentials
- PSR-7 HTTP Message implementation (or use built-in message factory)
- PSR-17 HTTP Factory implementation (or user built-in factory)

## Installation

```bash
composer require academe/elavon-epg-psr7
```

## Quick Start

Here's a complete example creating a credit card payment using Guzzle:

```php
<?php

use Academe\Elavon\Epg\Psr7\Messages\Request\Transaction\CreateTransactionRequest;
use Academe\Elavon\Epg\Psr7\Messages\Response\Transaction\TransactionResponse;
use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
use GuzzleHttp\Client;

// 1. Configure the API factory (reusable for multiple requests)
$factory = ElavonApiFactory::configure()
    ->withRegion('eu')                                    // 'eu' or 'us'
    ->withEnvironment('sandbox')                          // 'sandbox' or 'live'
    ->withAuthentication($merchantAlias, $apiSecret);     // Your credentials

// 2. Create the transaction request
$request = CreateTransactionRequest::fromData([
    'transaction' => [
        'total' => [
            'amount' => '99.99',
            'currencyCode' => 'USD',
        ],
        'card' => [
            'number' => '4111111111111111',
            'securityCode' => '123',
            'expirationMonth' => 12,
            'expirationYear' => 2025,
            'holderName' => 'John Doe',
        ],
        'description' => 'Order #12345',
    ],
]);

// 3. Build and apply API configuration
$psr7Request = $factory->apply($request->build());

// 4. Send with Guzzle (or any PSR-18 client)
$client = new Client();
$psr7Response = $client->sendRequest($psr7Request);

// 5. Parse the response
$response = TransactionResponse::fromPsr7Response($psr7Response);

if ($response->isSuccessful()) {
    echo "Transaction ID: {$response->transaction->id}\n";
    echo "State: {$response->transaction->state->value}\n";
} else {
    echo "Error: {$response->error->getMessage()}\n";
}
```

### Using DTOs (fromData)

You can create DTOs explicitly from array data using `fromData()`:

```php
use Academe\Elavon\Epg\Psr7\Dtos\Transaction;

$transaction = Transaction::fromData([
    // Note: major units, but can also supply ['amountMinor' => 9999, ...]
    'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
    'card' => [
        'number' => '4111111111111111',
        'securityCode' => '123',
        'expirationMonth' => 12,
        'expirationYear' => 2025,
        'holderName' => 'John Doe',
    ],
    'description' => 'Order #12345',
]);

$request = new CreateTransactionRequest($transaction);
```

### Using Value Objects (Type-Safe)

For full type safety, construct DTOs with typed value objects:

```php
use Academe\Elavon\Epg\Psr7\Dtos\Transaction;
use Academe\Elavon\Epg\Psr7\Dtos\Card;
use Academe\Elavon\Epg\Psr7\Enums\Currency;
use Money\Money;

$transaction = new Transaction(
    total: new Money('9999', Currency::USD), // Note: minor units
    card: new Card(
        number: '4111111111111111',
        securityCode: '123',
        expirationMonth: 12,
        expirationYear: 2025,
        holderName: 'John Doe',
    ),
    description: 'Order #12345',
);

$request = new CreateTransactionRequest($transaction);
```

### Paginated Lists with QueryParams

Many API endpoints return paginated lists of resources. The `QueryParams` class provides a fluent interface for pagination and filtering:

```php
use Academe\Elavon\Epg\Psr7\Dtos\QueryParams;
use Academe\Elavon\Epg\Psr7\Messages\Request\Order\RetrieveOrderListRequest;
use Academe\Elavon\Epg\Psr7\Messages\Response\Order\OrderListResponse;

// First page with pagination
$queryParams = QueryParams::create()->withLimit(25);
$request = (new RetrieveOrderListRequest($queryParams))->build();
$request = $factory->apply($request);

$response = OrderListResponse::fromPsr7Response($client->sendRequest($request));

if ($response->isSuccessful()) {
    foreach ($response->orders as $order) {
        echo "Order: {$order->id} - {$order->description}\n";
    }

    // Fetch next page using the nextPageToken
    if ($response->nextPageToken) {
        $nextParams = QueryParams::create()
            ->withLimit(25)
            ->withPageToken($response->nextPageToken);
        $nextRequest = (new RetrieveOrderListRequest($nextParams))->build();
        // ...
    }
}
```

#### QueryParams Methods

| Method | Description |
| ------ | ----------- |
| `QueryParams::create()` | Create an empty QueryParams instance |
| `withPageToken(?string $token)` | Set the pagination token for the next page |
| `withLimit(?int $limit)` | Set page size (1-200, throws exception if out of range) |
| `withFilter(string $field, QueryFilterOperator $operator, string $value)` | Add a filter condition |
| `isEmpty()` | Check if any parameters are set |
| `apply(UriInterface $uri)` | Apply parameters to a PSR-7 URI |

#### Filtering

The Elavon API supports filtering on list endpoints. Use `withFilter()` with the `QueryFilterOperator` enum to add filter conditions:

```php
use Academe\Elavon\Epg\Psr7\Enums\QueryFilterOperator;

$queryParams = QueryParams::create()
    ->withLimit(50)
    ->withFilter('createdAt', QueryFilterOperator::GT, '2024-01-01')
    ->withFilter('type', QueryFilterOperator::EQ, 'refund')
    ->withFilter('total.amount', QueryFilterOperator::GE, '100');
```

**Available filter operators (QueryFilterOperator enum):**

| Enum Case | Value | Description |
| --------- | ----- | ----------- |
| `EQ` | `eq` | Equals |
| `NE` | `ne` | Not equals |
| `GT` | `gt` | Greater than |
| `GE` | `ge` | Greater than or equal |
| `LT` | `lt` | Less than |
| `LE` | `le` | Less than or equal |
| `LIKE` | `like` | Like pattern |
| `IN` | `in` | In list |
| `CONTAINS` | `contains` | Contains |
| `IS` | `is` | Is (for null checks) |
| `IS_NOT` | `isnot` | Is not (for null checks) |

**Note:** Available filters vary by endpoint. See the [Elavon API documentation](https://developer.elavon.com/products/en-uk/elavon-payment-gateway/v1/api-reference) for endpoint-specific filters.

#### Response Pagination Properties

| Property | Description |
| -------- | ----------- |
| `$response->nextPageToken` | Token for the next page (use with `withPageToken()`) |
| `$response->nextPage` | Full URL for the next page |
| `$response->firstPage` | Full URL to return to the first page |
| `$response->hasMorePages()` | Returns `true` if more pages are available |

#### List Requests Supporting QueryParams

The following request classes accept a `QueryParams` parameter:

**Top-level resources:**

- `RetrieveAccountListRequest` → GET /accounts
- `RetrieveBatchListRequest` → GET /batches
- `RetrieveHostedCardListRequest` → GET /hosted-cards
- `RetrieveManualBatchListRequest` → GET /manual-batches
- `RetrieveMerchantListRequest` → GET /merchants
- `RetrieveNotificationListRequest` → GET /notifications
- `RetrieveOrderListRequest` → GET /orders
- `RetrievePaymentLinkEventListRequest` → GET /payment-link-events
- `RetrievePaymentLinkListRequest` → GET /payment-links
- `RetrievePaymentMethodLinkListRequest` → GET /payment-method-links
- `RetrievePlanListListRequest` → GET /plan-lists
- `RetrieveProcessorAccountListRequest` → GET /processor-accounts
- `RetrieveShopperListRequest` → GET /shoppers
- `RetrieveStoredAchPaymentListRequest` → GET /stored-ach-payments
- `RetrieveStoredCardListRequest` → GET /stored-cards
- `RetrieveTerminalListRequest` → GET /terminals
- `RetrieveTransactionListRequest` → GET /transactions

**Nested resources (require parent ID + QueryParams):**

- `RetrieveEmvKeyListRequest` → GET /terminals/{id}/emv-keys
- `RetrievePaymentLinkEventListRequest` → GET /payment-links/{id}/events
- `RetrieveProvisioningCodeListRequest` → GET /terminals/{id}/provisioning-codes
- `RetrieveShopperStoredCardsRequest` → GET /shoppers/{id}/stored-cards
- `RetrieveTotalAdjustmentListRequest` → GET /transactions/{id}/total-adjustments

See [docs/examples/](docs/examples/) for more examples.

## Features

Based on the Elavon Payment Gateway API version 2025-10-01, this package supports:

### Resources
- Merchants
- Processor Accounts
- Terminals
- Accounts
- Orders
- Payment Links & Payment Link Events
- Payment Sessions
- Payment Method Links & Payment Method Sessions
- Apple Pay Payment Sessions
- Hosted Cards & Hosted ACH Payments
- HSM Cards
- Forex Advices
- Transactions
- Apple Pay, Google Pay, and Paze Payments
- Surcharge & Refund Surcharge Advices
- Batches & Manual Batches
- Shoppers
- Stored Cards & Stored ACH Payments
- Plans & Subscriptions
- Notifications
- Total Adjustments

## API Endpoints

The Elavon Payment Gateway API base URLs:
- EU Sandbox: `https://uat.api.converge.eu.elavonaws.com`
- EU Production: `https://api.eu.convergepay.com`
- US Sandbox: `https://uat.api.convergepay.com`
- US Production: `https://api.convergepay.com`

## Development

### Testing

```bash
composer test
```

### Code Quality

```bash
composer phpstan
composer cs-check
composer cs-fix
```

## License

MIT License. See [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome. Please ensure all tests pass and code follows PSR-12 coding standards.

## Namespace

All classes are under the `Academe\Elavon\Epg\Psr7\` namespace.
