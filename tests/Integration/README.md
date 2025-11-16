# Integration Tests

This directory contains integration tests that make real API calls to the Opayo/Elavon Payment Gateway sandbox environment.

## Setup

### 1. Create .env file

Copy the `.env.example` file to `.env` and fill in your Elavon UAT credentials:

```bash
cp .env.example .env
```

Edit the `.env` file with your test credentials:

```env
ELAVON_MERCHANT_ALIAS=your_test_merchant_alias
ELAVON_API_KEY=your_test_api_key
ELAVON_BASE_URI=https://uat.api.converge.eu.elavonaws.com
```

### 2. Obtain Test Credentials

To get UAT/sandbox credentials:

1. Contact Elavon support to request a UAT merchant account
2. You will receive a merchant alias and API key for testing
3. Use the appropriate base URI for your region:
   - EU UAT/Test: `https://uat.api.converge.eu.elavonaws.com`
   - EU Production: `https://api.eu.elavonpayments.com` (do not use for testing!)
4. See the official documentation: https://developer.elavon.com/products/en-uk/elavon-payment-gateway/v1/overview

## Running Integration Tests

### Run all integration tests

```bash
composer test:integration
```

Or using PHPUnit directly:

```bash
vendor/bin/phpunit --testsuite Integration
```

### Run unit tests only (default)

```bash
composer test
# or
vendor/bin/phpunit --testsuite Unit
```

### Run all tests (unit + integration)

```bash
vendor/bin/phpunit --exclude-group none
```

## Test Cards

The integration tests use standard test card numbers:

- **Successful Authorization**: `4111111111111111`
- **Declined**: `4000000000000002`
- **Expired**: Use any expired expiration date

All test cards:
- CVV: Any 3 digits (e.g., `123`)
- Expiration: Any future date (e.g., `12/2025`)

## Tests Included

### `test_createTransaction_withValidCard_returnsAuthorized()`

Tests a successful credit card authorization:
- Creates a transaction with a valid test card
- Verifies the transaction is authorized or captured
- Checks all response fields are populated correctly

### `test_createTransaction_withDeclinedCard_returnsDeclined()`

Tests a declined transaction:
- Creates a transaction with a test card that always declines
- Verifies the transaction state is DECLINED

## Notes

- Integration tests are marked with `@group integration` annotation
- By default, `composer test` excludes integration tests (they require credentials)
- Integration tests are skipped automatically if credentials are not configured
- Tests output transaction details to stdout for debugging
- Each test uses a unique custom reference with timestamp to avoid conflicts

## Troubleshooting

### Tests are skipped

**Problem**: Tests show as skipped with message about missing credentials.

**Solution**: Ensure you have created a `.env` file with valid credentials (see Setup above).

### Authentication errors (401)

**Problem**: Tests fail with HTTP 401 Unauthorized.

**Solution**:
- Verify your `ELAVON_MERCHANT_ALIAS` and `ELAVON_API_KEY` are correct
- Ensure you're using UAT credentials, not production
- Check that your UAT account is active

### Connection errors

**Problem**: Tests fail with connection timeout or network errors.

**Solution**:
- Verify the `ELAVON_BASE_URI` is correct (`https://uat.api.converge.eu.elavonaws.com`)
- Check your internet connection
- Verify the UAT environment is operational
- Check Elavon's status page for any outages

### Unexpected transaction state

**Problem**: Test expects AUTHORIZED but gets CAPTURED (or vice versa).

**Solution**: This is normal - some merchant accounts are configured for auto-capture. The test accounts for both states.

## Expanding Tests

To add more integration tests:

1. Create new test methods in `OpayoIntegrationTest.php`
2. Mark them with `@group integration` annotation
3. Use different test scenarios (amounts, currencies, card types)
4. Consider testing error cases (invalid cards, expired cards, etc.)

Future test ideas:
- Different currencies (EUR, GBP, CAD, etc.)
- Different card schemes (Mastercard, Amex, etc.)
- Edge cases (minimum/maximum amounts)
- Transaction with all optional fields
- Error handling for malformed requests
