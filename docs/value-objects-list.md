# Value Objects List

This document lists all value objects identified from the Elavon Payment Gateway OpenAPI specification (version 2025-10-01).

## Philosophy: When to Create Value Objects

**Create a value object when it provides:**
1. ✅ **Validation** - Complex format rules (e.g., card number patterns)
2. ✅ **Behavior** - Useful methods beyond simple getters (e.g., `Money::isPositive()`)
3. ✅ **Type Safety** - Prevents mixing concepts (e.g., currency code vs country code)
4. ✅ **Reusability** - Used consistently across multiple DTOs

**DON'T create a value object for:**
- ❌ Simple strings without validation (e.g., `last4`, `fingerprint`, `description`)
- ❌ Read-only API responses (e.g., `maskedCardNumber`, `bin`)
- ❌ Simple IDs and references (just use `string` type)
- ❌ Boolean flags or simple integers

**Examples:**
- `CardNumber` → ✅ Value object (complex validation, writeOnly, reused)
- `last4` → ❌ Plain string property on Card DTO
- `Money` → ✅ Value object (validation, behavior like `isPositive()`)
- `description` → ❌ Plain string property

## Implementation Priority

### Phase 1: Core Money & Amounts
- ✅ Money (AmountAndCurrency) - **Includes validation methods for positive/negative/zero checks**
  - No separate classes needed for PositiveAmountAndCurrency or NonNegativeAmountAndCurrency
  - Validation happens at point of use in DTOs/requests

### Phase 2: Card Types (Input Only)

- CardNumber (validation + writeOnly)
- CardSecurityCode (validation + writeOnly)

**Note:** Other card fields are simple properties on DTOs:

- `expirationMonth` / `expirationYear` - Simple integers with range validation in DTO
- `last4`, `bin`, `maskedCardNumber`, `fingerprint`, `par` - Read-only strings (no value objects needed)

### Phase 3: Basic Types (With Validation)

- EmailAddress (format validation)
- PhoneNumber (pattern validation)

**Note:** Most identifiers and codes are simple strings:

- `countryCode`, `currencyCode`, `languageTag` - Use Currency enum or plain strings
- `postalCode` - Simple string (validation varies by country, handle in DTO if needed)
- `resourceId`, `customReference`, `description` - Plain strings
- Most timestamps - Use native `DateTimeImmutable` or string

### Phase 4: Bank Account Types

- BankRoutingNumber (validation + writeOnly)
- BankAccountNumber (validation + writeOnly)

**Note:** `achFingerprint`, `blikCode` - Read-only strings on DTOs

### Phase 5: Complex/Composite Types (As Needed)

These may become DTOs rather than value objects:

- Address (composite - may just be properties on parent DTO)
- 3-D Secure types (may be DTO properties rather than separate value objects)

## Complete Catalog

### 1. Money/Currency Types (1 type)

| Value Object | Type | Validation | Description |
|--------------|------|------------|-------------|
| Money (AmountAndCurrency) | Composite | amount: max 9 integer + 4 fractional digits<br>currencyCode: 3-letter ISO 4217 | Standard currency representation with built-in validation methods |

**Note:** The OpenAPI spec references `PositiveAmountAndCurrency` and `NonNegativeAmountAndCurrency`, but these are handled by the single `Money` class with validation at the point of use:

- Use `Money::isPositive()` to check for positive amounts
- Use `Money::isNegative()` to check for negative amounts
- Use `Money::isZero()` to check for zero amounts
- Validation happens in DTOs/request messages where the constraint is needed

### 2. Card-Related Types (10 types)

| Value Object | Type | Validation | Sensitive | Description |
|--------------|------|------------|-----------|-------------|
| CardNumber | string | Pattern: `\D*(?:\d\D*){13,19}`<br>Length: 13-23 | writeOnly | PAN |
| CardSecurityCode | string | Pattern: `\d{3,4}`<br>Length: 3-4 | writeOnly | CVV/CVC |
| CardExpirationMonth | integer | Min: 1, Max: 12 | No | Month |
| CardExpirationYear | integer | Min: 2000, Max: 2099 | No | Year |
| CardLast4 | string | Pattern: `\d{4}` | readOnly | Last 4 digits |
| CardBin | string | Pattern: `\d{6}` | readOnly | BIN/IIN (first 6) |
| MaskedCardNumber | string | Pattern: `\D+(\d\D*){4}` | readOnly | Masked PAN |
| PanFingerprint | string | - | readOnly | PAN fingerprint |
| PaymentAccountReference | string | - | readOnly | PAR value |
| ValueToken | string | - | No | Token for sensitive values |

### 3. ACH/Bank Account Types (4 types)

| Value Object | Type | Validation | Description |
|--------------|------|------------|-------------|
| BankRoutingNumber | string | Pattern: `^\d{9}?` | 9-digit routing number |
| BankAccountNumber | string | Pattern: `^\d{5,16}?` | 5-16 digit account number |
| AchFingerprint | string | - (readOnly) | ACH fingerprint |
| BlikCode | string | Pattern: `\d{6}` | 6-digit BLIK code |

### 4. Contact Information (4 types)

| Value Object | Type | Validation | Description |
|--------------|------|------------|-------------|
| EmailAddress | string | Max: 254 chars | Standard email |
| PhoneNumber | string | Pattern: `[\w \-+:()/]*`<br>Max: 255 | International phone |
| ShopperStatementPhone | string | Pattern: `[\w \-+:()/]*`<br>Max: 20 | Statement phone |
| WebsiteUrl | string | URL format | Business website |

### 5. URL Types (4 types)

| Value Object | Type | Validation | Description |
|--------------|------|------------|-------------|
| ResourceUrl | string | Format: url | Resource self-link |
| ReturnUrl | string | Pattern: `https?://[^/]{2,}.*`<br>Max: 2048 | Redirect URL |
| CancelUrl | string | Pattern: `https?://[^/]{2,}.*`<br>Max: 2048 | Cancel URL |
| OriginUrl | string | Pattern: `https?://[^/]{2,}.*`<br>Max: 2048 | Origin URL |

### 6. Timestamp Types (5+ types)

| Value Object | Type | Format | Description |
|--------------|------|--------|-------------|
| CreatedAt | string | date-time (ISO 8601) | Creation timestamp |
| ModifiedAt | string | date-time (ISO 8601) | Modification timestamp |
| ExpiresAt | string | date-time (ISO 8601) | Expiration timestamp |
| DeletedAt | string | date-time (ISO 8601) | Deletion timestamp |
| BillDate | string | date (YYYY-MM-DD) | Billing date |

### 7. Identifiers & References (6 types)

| Value Object | Type | Max Length | Description |
|--------------|------|------------|-------------|
| ResourceId | string | - (readOnly) | Server-assigned ID |
| CustomReference | string | 255 | Merchant reference |
| OrderReference | string | 255 | Order reference |
| ShopperReference | string | 255 | Shopper reference |
| ProcessorReference | string | - (readOnly) | Processor reference |
| PurchaserReference | string | - | Purchaser reference |

### 8. Address Components (5 types)

| Value Object | Type | Validation | Description |
|--------------|------|------------|-------------|
| Street1 | string | Pattern: `[^%<>/\[\]{}\\]*`<br>Max: 255 | Address line 1 |
| Street2 | string | Pattern: `[^%<>/\[\]{}\\]*`<br>Max: 255 | Address line 2 |
| City | string | Pattern: `[^%<>/\[\]{}\\]*`<br>Max: 255 | City |
| Region | string | Pattern: `[^%<>/\[\]{}\\]*`<br>Max: 255 | State/Province |
| PostalCode | string | Pattern: `[^%<>/\[\]{}\\]*`<br>Max: 255 | Postal/ZIP code |

### 9. ISO Codes (4 types)

| Value Object | Type | Length | Standard | Description |
|--------------|------|--------|----------|-------------|
| CountryCode | string | 3 | ISO 3166-1 Alpha-3 | Country code (e.g., "GBR") |
| CurrencyCode | string | 3 | ISO 4217 | Currency code (e.g., "USD") |
| LanguageTag | string | Max: 255 | IETF BCP 47 | Language tag (e.g., "en-GB") |
| TimeZoneId | string | - | IANA TZ Database | Timezone (e.g., "Europe/London") |

### 10. Business Information (4 types)

| Value Object | Type | Validation | Description |
|--------------|------|------------|-------------|
| MerchantCategoryCode | string | 4 digits | ISO 18245 MCC |
| LegalName | string | - | Legal business name |
| FriendlyName | string | - | Display name |
| TradeName | string | - | Trade/DBA name |

### 11. 3-D Secure Types (6 types)

| Value Object | Type | Validation | Description |
|--------------|------|------------|-------------|
| DirectoryServerTransactionId | string | UUID format | DS transaction ID |
| TransactionStatus | string | Pattern: `[YNUA]` | Auth outcome |
| ElectronicCommerceIndicator | string | Pattern: `0?[012567]` | ECI value |
| AuthenticationValue | string | 28 chars (Base64) | CAVV/AAV value |
| ProtocolVersion | string | Pattern: `\d+.\d+.\d+` | 3DS version |
| Cryptogram | string | - | Cryptogram value |

### 12. Shopper Statement Types (3 types)

| Value Object | Type | Validation | Description |
|--------------|------|------------|-------------|
| ShopperStatementName | string | Pattern: `[^%<>/\[\]{}\\]*`<br>Max: 25 | Statement descriptor |
| ShopperStatementUrl | string | Max: 13 | Statement URL |
| ShopperStatementPhone | string | Pattern: `[\w \-+:()/]*`<br>Max: 20 | Statement phone |

### 13. Rates & Percentages (3 types)

| Value Object | Type | Format | Description |
|--------------|------|--------|-------------|
| SurchargeRate | string | Decimal (e.g., "0.0399" = 3.99%) | Surcharge rate |
| MarkupRate | string | Decimal (e.g., "0.035" = 3.5%) | Markup rate |
| ConversionRate | string | Decimal (e.g., "11.89") | FX rate |

### 14. Paze-Specific Types (2 types)

| Value Object | Type | Max Length | Description |
|--------------|------|------------|-------------|
| PayloadId | string | 64 | Paze payload ID |
| SessionId | string | 255 | Paze session ID |

### 15. Other Types (3 types)

| Value Object | Type | Validation | Description |
|--------------|------|------------|-------------|
| Description | string | Max: 255<br>Pattern: `[^%<>/\[\]{}\\]*` | General description |
| InvoiceNumber | string | - | Invoice number |
| ShippingDate | string | date | Shipping date |

## Enumerations (45 types)

See [enums-list.md](enums-list.md) for the complete list of enumeration types.

## Notes

- **writeOnly**: Value is accepted in requests but never returned in responses
- **readOnly**: Value is only returned in responses, never accepted in requests
- **Composite**: Value object composed of multiple fields
- Validation patterns use PCRE regex syntax

## References

- OpenAPI Specification: [docs/openapi.json](openapi.json)
- ISO 4217: Currency codes
- ISO 3166-1: Country codes
- ISO 18245: Merchant category codes
- IETF BCP 47: Language tags
