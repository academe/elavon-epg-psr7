# Changes - Elavon API Endpoint Update

## Summary

Updated all API endpoints and documentation to use the correct Elavon Payment Gateway URLs based on official documentation at https://developer.elavon.com/.

## Issues Resolved

1. ✅ **Fixed incorrect API endpoint** - Changed from `api.eu.sandbox.convergepay.com` (invalid) to `uat.api.converge.eu.elavonaws.com` (correct UAT endpoint)
2. ✅ **Fixed Guzzle deprecation warnings** - Updated to use Guzzle ^7.5|^8.0 (warnings gone with current lock)

## Files Changed

### Configuration Files

1. **[.env.example](.env.example)**
   - Changed `OPAYO_*` variables to `ELAVON_*`
   - Updated base URI from `https://api.eu.sandbox.convergepay.com` to `https://uat.api.converge.eu.elavonaws.com`
   - Added reference to official documentation
   - Added production URL for reference

### Source Code

2. **[src/Messages/Request/CreateTransactionRequest.php](src/Messages/Request/CreateTransactionRequest.php)**
   - Updated default `$baseUri` from `https://api.eu.convergepay.com` to `https://api.eu.elavonpayments.com`
   - Updated PHPDoc example

### Tests

3. **[tests/Integration/OpayoIntegrationTest.php](tests/Integration/OpayoIntegrationTest.php)**
   - Changed environment variables from `OPAYO_*` to `ELAVON_*`
   - Updated default base URI to `https://uat.api.converge.eu.elavonaws.com`
   - Updated class documentation

4. **[tests/Unit/Messages/Request/CreateTransactionRequestTest.php](tests/Unit/Messages/Request/CreateTransactionRequestTest.php)**
   - Updated expected URLs in assertions to match new defaults
   - Changed custom URI test to use UAT endpoint

### Documentation

5. **[tests/Integration/README.md](tests/Integration/README.md)**
   - Updated all references from Opayo to Elavon
   - Changed environment variable names
   - Updated API endpoints
   - Added link to official documentation
   - Updated troubleshooting section

6. **[INTEGRATION_TESTS.md](INTEGRATION_TESTS.md)**
   - Updated all references to use correct Elavon terminology
   - Changed environment variable names
   - Updated API endpoints
   - Added official documentation links
   - Added "Host not found" troubleshooting entry

### Dependencies

7. **[composer.json](composer.json)** (already updated by user)
   - Guzzle version constraint: `^7.5|^8.0`

## API Endpoint Reference

### Correct Endpoints (Updated)

- **EU UAT/Test**: `https://uat.api.converge.eu.elavonaws.com`
- **EU Production**: `https://api.eu.elavonpayments.com`

### Previous Endpoints (Incorrect)

- ❌ `https://api.eu.sandbox.convergepay.com` (does not resolve)
- ❌ `https://api.us.sandbox.convergepay.com` (does not resolve)
- ❌ `https://api.eu.convergepay.com` (old default)

## Environment Variables

### Before (Incorrect)
```env
OPAYO_MERCHANT_ALIAS=...
OPAYO_API_KEY=...
OPAYO_BASE_URI=https://api.eu.sandbox.convergepay.com
```

### After (Correct)
```env
ELAVON_MERCHANT_ALIAS=...
ELAVON_API_KEY=...
ELAVON_BASE_URI=https://uat.api.converge.eu.elavonaws.com
```

## Testing Results

### Unit Tests
- ✅ All unit tests pass (except 2 pre-existing failures unrelated to these changes)
- ✅ No Guzzle deprecation warnings

### Integration Tests
- ✅ Properly skip when credentials not configured
- ✅ Ready to test against real Elavon UAT environment when credentials are provided

## Next Steps

To use the integration tests:

1. Obtain Elavon UAT credentials from Elavon support
2. Copy `.env.example` to `.env`
3. Fill in your credentials:
   ```env
   ELAVON_MERCHANT_ALIAS=your_merchant_alias
   ELAVON_API_KEY=your_api_key
   ELAVON_BASE_URI=https://uat.api.converge.eu.elavonaws.com
   ```
4. Run integration tests: `composer test:integration`

## References

- **Elavon Developer Portal**: https://developer.elavon.com/
- **API Documentation**: https://developer.elavon.com/products/en-uk/elavon-payment-gateway/v1/overview
