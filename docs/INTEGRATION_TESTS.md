# Integration Tests Setup

Integration tests have been added to test the credit card payment vertical stack against a real Elavon Payment Gateway UAT environment.

## What Was Added

### 1. Test Infrastructure

- **[tests/Integration/OpayoIntegrationTest.php](tests/Integration/OpayoIntegrationTest.php)** - Main integration test class
  - Tests successful card authorization
  - Tests declined card handling
  - Automatically skips if credentials not configured
  - Outputs transaction details for debugging

- **[tests/Integration/README.md](tests/Integration/README.md)** - Detailed documentation
  - Setup instructions
  - Test card numbers
  - Troubleshooting guide

### 2. Configuration Files

- **[.env.example](.env.example)** - Template for environment variables
  - `ELAVON_MERCHANT_ALIAS` - Your UAT merchant alias
  - `ELAVON_API_KEY` - Your UAT API key
  - `ELAVON_BASE_URI` - UAT API URL (`https://uat.api.converge.eu.elavonaws.com`)

- **.env** - (Not committed) Your actual credentials
  - Copy from `.env.example` and fill in your values
  - Already in `.gitignore` to prevent accidental commits

### 3. Dependencies

- **guzzlehttp/guzzle** `^7.5` - HTTP client for making real API requests
  - Only in dev dependencies
  - Uses PSR-18 ClientInterface

### 4. PHPUnit Configuration

Updated [phpunit.xml.dist](phpunit.xml.dist):
- Separated Unit and Integration test suites
- Integration tests excluded by default (requires credentials)

### 5. Composer Scripts

Updated [composer.json](composer.json) scripts:
```json
{
  "test": "phpunit --testsuite Unit",          // Run only unit tests (default)
  "test:unit": "phpunit --testsuite Unit",     // Explicitly run unit tests
  "test:integration": "phpunit --testsuite Integration --exclude-group none",  // Run integration tests
  "test:all": "phpunit --exclude-group none"   // Run all tests (unit + integration)
}
```

## Quick Start

### 1. Setup Credentials

```bash
# Copy example file
cp .env.example .env

# Edit .env with your Elavon UAT credentials
# ELAVON_MERCHANT_ALIAS=your_merchant_alias
# ELAVON_API_KEY=your_api_key
# ELAVON_BASE_URI=https://uat.api.converge.eu.elavonaws.com
```

### 2. Run Tests

```bash
# Run integration tests
composer test:integration

# Run unit tests (default)
composer test

# Run all tests
composer test:all
```

## Test Coverage

### Current Tests

1. **test_createTransaction_withValidCard_returnsAuthorized()**
   - Creates a $10.00 USD transaction
   - Uses test card: 4111111111111111
   - Verifies transaction is AUTHORIZED or CAPTURED
   - Checks all response fields

2. **test_createTransaction_withDeclinedCard_returnsDeclined()**
   - Creates a $10.00 USD transaction
   - Uses test card: 4000000000000002 (always declines)
   - Verifies transaction state is DECLINED

### Test Cards

- **Success**: `4111111111111111`
- **Declined**: `4000000000000002`
- **CVV**: Any 3 digits (e.g., `123`)
- **Expiry**: Any future date (e.g., `12/2025`)

## How It Works

### Test Flow

1. **Setup**: Load credentials from `.env` file
2. **Skip Check**: If credentials missing, test is skipped with message
3. **Request**: Create PSR-7 request using vertical stack classes
4. **Send**: Use Guzzle HTTP client with Basic Auth
5. **Parse**: Parse response using `TransactionResponse`
6. **Assert**: Verify transaction state and fields
7. **Debug**: Output transaction details to stdout

### Example Output

```
Transaction ID: txn_abc123def456
State: AUTHORIZED
Amount: 10.00 USD
Card Last 4: 1111
Card Scheme: VISA
Created At: 2025-01-15T10:30:45Z
```

## Expanding Tests

Future test ideas:

### Additional Scenarios
- [ ] Different currencies (EUR, GBP, CAD)
- [ ] Different card schemes (Mastercard, Amex)
- [ ] Different amounts (minimum, maximum, decimal precision)
- [ ] All optional fields populated
- [ ] Special characters in description/reference

### Transaction Operations
- [ ] Capture authorized transaction
- [ ] Void transaction
- [ ] Refund transaction
- [ ] Partial refund

### Error Cases
- [ ] Invalid card number format
- [ ] Expired card
- [ ] Invalid CVV
- [ ] Insufficient funds
- [ ] Network timeout handling
- [ ] Invalid authentication

### Performance
- [ ] Concurrent requests
- [ ] Response time benchmarks

## Notes

- Integration tests are **not run** by default with `composer test`
- Tests **automatically skip** if credentials are missing (won't fail the build)
- Each test uses a unique `customReference` with timestamp
- Tests output transaction details for debugging/verification
- The Guzzle deprecation warnings are from PHP 8.4 strict typing and are normal

## Troubleshooting

See [tests/Integration/README.md](tests/Integration/README.md) for detailed troubleshooting guide.

Common issues:
- **Tests skipped**: Missing `.env` file or credentials
- **401 Unauthorized**: Invalid credentials
- **Connection timeout**: Check `ELAVON_BASE_URI` and internet connection (should be `https://uat.api.converge.eu.elavonaws.com`)
- **Host not found**: Using incorrect API endpoint (make sure to use the correct Elavon API URL)
- **Unexpected state**: Merchant config (auto-capture vs auth-only)

## Additional Resources

- **Elavon Developer Portal**: https://developer.elavon.com/
- **API Documentation**: https://developer.elavon.com/products/en-uk/elavon-payment-gateway/v1/overview
- **Support**: Contact Elavon support for UAT credentials and assistance
