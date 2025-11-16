# Transaction Model Properties Documentation

This document catalogs all properties of the Transaction model from the Elavon Payment Gateway API, their correct data types, and implementation status.

## Implementation Status Legend

- ✅ **Implemented** - Property exists with correct type
- ⚠️ **Partial** - Property exists but needs type refinement
- ❌ **Missing** - Property not yet implemented
- 🔄 **In Progress** - Currently being worked on

---

## Core Properties

### Identity & State

| Property | Current Type | Correct Type | Status | Notes |
|----------|-------------|--------------|--------|-------|
| `id` | `?string` | `?string` | ✅ | Resource ID assigned by server (readOnly) |
| `state` | `?TransactionState` | `?TransactionState` | ✅ | Current transaction state enum (readOnly) |
| `type` | - | `?TransactionType` | ❌ | Type of transaction (sale/refund/void) |

### Timestamps

| Property | Current Type | Correct Type | Status | Notes |
|----------|-------------|--------------|--------|-------|
| `createdAt` | `?string` | `?DateTimeImmutable` | ⚠️ | ISO 8601 date-time (readOnly) |
| `modifiedAt` | `?string` | `?DateTimeImmutable` | ⚠️ | ISO 8601 date-time (readOnly) |
| `authorizationExpiresAt` | `?string` | `?DateTimeImmutable` | ⚠️ | ISO 8601 date-time (readOnly) |
| `refundableUntil` | `?string` | `?DateTimeImmutable` | ⚠️ | ISO 8601 date-time (readOnly) |
| `shippingDate` | `?string` | `?DateTimeImmutable` | ⚠️ | ISO 8601 date (request) |

---

## Money & Financial

| Property | Current Type | Correct Type | Status | Notes |
|----------|-------------|--------------|--------|-------|
| `total` | `?Money` | `?Money` | ✅ | Transaction total (PositiveAmountAndCurrency) |
| `totalRefunded` | - | `?Money` | ❌ | Sum of all refunds (readOnly) |
| `issuerTotal` | - | `?Money` | ❌ | Total in target currency (readOnly) |
| `tip` | - | `?Money` | ❌ | Tip amount (NonNegativeAmountAndCurrency) |
| `salesTax` | - | `?Money` | ❌ | Sales tax (NonNegativeAmountAndCurrency) |
| `conversionRate` | - | `?string` | ❌ | Currency exchange rate (readOnly) |
| `markupRate` | - | `?string` | ❌ | Markup percent (readOnly) |
| `markupRateAnnotation` | - | `?MarkupRateAnnotation` | ❌ | Enum for markup rate annotation |
| `rateProviderName` | - | `?string` | ❌ | Rate provider name (readOnly) |

---

## Payment Method

| Property | Current Type | Correct Type | Status | Notes |
|----------|-------------|--------------|--------|-------|
| `card` | `?Card` | `?Card` | ✅ | Card payment details |
| `paymentMethod` | - | `?PaymentMethod` | ❌ | Payment method enum (readOnly) |
| `paymentMethodOrigin` | - | `?PaymentMethodOrigin` | ❌ | Origin enum (readOnly) |
| `paymentMethodQualifier` | - | `?PaymentMethodQualifier` | ❌ | Qualifier enum (readOnly) |
| `ach` | - | `?Ach` | ❌ | ACH payment details |
| `wallet` | - | `?Wallet` | ❌ | Digital wallet details |

---

## Resource URLs (Relationships)

| Property | Current Type | Correct Type | Status | Notes |
|----------|-------------|--------------|--------|-------|
| `href` | `?string` | `?string` | ✅ | Self link (readOnly) |
| `merchant` | `?string` | `?string` | ✅ | Merchant resource URL (readOnly) |
| `processorAccount` | - | `?string` | ❌ | ProcessorAccount resource URL (readOnly) |
| `account` | - | `?string` | ❌ | Account resource URL (readOnly) |
| `terminal` | `?string` | `?string` | ✅ | Terminal resource URL |
| `parentTransaction` | `?string` | `?string` | ✅ | Parent transaction URL (readOnly) |
| `hostedCard` | `?string` | `?string` | ✅ | HostedCard resource URL |
| `hsmCard` | `?string` | `?string` | ✅ | HsmCard resource URL |
| `storedCard` | `?string` | `?string` | ✅ | StoredCard resource URL |
| `paymentLink` | `?string` | `?string` | ✅ | PaymentLink resource URL |
| `paymentSession` | `?string` | `?string` | ✅ | PaymentSession resource URL |
| `batch` | `?string` | `?string` | ✅ | Batch resource URL (readOnly) |
| `manualBatch` | `?string` | `?string` | ✅ | ManualBatch resource URL (readOnly) |
| `forexAdvice` | - | `?string` | ❌ | ForexAdvice resource URL (readOnly) |
| `shopper` | - | `?string` | ❌ | Shopper resource URL (readOnly) |
| `order` | - | `?string` | ❌ | Order resource URL (readOnly) |

---

## References & Identifiers

| Property | Current Type | Correct Type | Status | Notes |
|----------|-------------|--------------|--------|-------|
| `customReference` | `?string` | `?string` | ✅ | Optional merchant reference (max 255) |
| `orderReference` | `?string` | `?string` | ✅ | Order reference (readOnly, max 255) |
| `shopperReference` | `?string` | `?string` | ✅ | Shopper reference like PO number (readOnly, max 255) |
| `purchaserReference` | `?string` | `?string` | ✅ | Purchaser identifier (readOnly, max 17) |
| `processorReference` | `?string` | `?string` | ✅ | Processor-assigned reference (readOnly) |
| `issuerReference` | `?string` | `?string` | ✅ | Card issuer-assigned reference (readOnly) |
| `invoiceNumber` | `?string` | `?string` | ✅ | Invoice number (max 25) |
| `processorBatchReference` | `?string` | `?string` | ✅ | Processor batch reference (readOnly) |

---

## Shopper Information

| Property | Current Type | Correct Type | Status | Notes |
|----------|-------------|--------------|--------|-------|
| `shopperEmailAddress` | `?string` | `?string` | ✅ | Email address (could be EmailAddress VO) |
| `shopperIpAddress` | `?string` | `?string` | ✅ | IP address (could be IpAddress VO) |
| `shopperLanguageTag` | `?string` | `?string` | ✅ | IETF BCP 47 language tag |
| `shopperTimeZone` | `?string` | `?string` | ✅ | IANA timezone |
| `shopperInteraction` | - | `?ShopperInteraction` | ❌ | Shopper interaction enum (readOnly) |
| `shopperStatement` | - | `?ShopperStatement` | ❌ | Statement descriptor overrides (readOnly) |
| `shipTo` | - | `?Contact` | ❌ | Shipping contact details (readOnly) |
| `billTo` | - | `?Contact` | ❌ | Billing contact details |

---

## Processing Details

| Property | Current Type | Correct Type | Status | Notes |
|----------|-------------|--------------|--------|-------|
| `processorDirective` | - | `?ProcessorDirective` | ❌ | Processor directive enum (readOnly) |
| `source` | - | `?Source` | ❌ | Transaction source enum (readOnly) |
| `marketSegment` | - | `?MarketSegment` | ❌ | Market segment enum |
| `isAuthorized` | - | `?bool` | ❌ | Whether transaction was authorized (readOnly) |
| `isVoided` | - | `?bool` | ❌ | Whether transaction was voided (readOnly) |
| `isRefunded` | - | `?bool` | ❌ | Whether transaction was refunded (readOnly) |
| `isReversed` | - | `?bool` | ❌ | Whether transaction was reversed (readOnly) |
| `isCaptured` | - | `?bool` | ❌ | Whether transaction was captured (readOnly) |
| `isSettled` | - | `?bool` | ❌ | Whether transaction was settled (readOnly) |
| `isPartiallyRefunded` | - | `?bool` | ❌ | Whether partially refunded (readOnly) |
| `failures` | - | `?array<Failure>` | ❌ | Array of failure objects (readOnly) |

---

## Credential On File

| Property | Current Type | Correct Type | Status | Notes |
|----------|-------------|--------------|--------|-------|
| `credentialOnFileData` | `?string` | `?CredentialOnFileData` | ⚠️ | Should be an object/DTO, not string |
| `credentialOnFile` | - | `?CredentialOnFile` | ❌ | Credential on file object |

---

## 3-D Secure

| Property | Current Type | Correct Type | Status | Notes |
|----------|-------------|--------------|--------|-------|
| `threeDSecure` | - | `?ThreeDSecure` | ❌ | 3DS authentication data |

---

## Surcharge

| Property | Current Type | Correct Type | Status | Notes |
|----------|-------------|--------------|--------|-------|
| `surcharge` | - | `?Surcharge` | ❌ | Surcharge details (readOnly) |

---

## Debtor Account (MCC 6012/6050/6051)

| Property | Current Type | Correct Type | Status | Notes |
|----------|-------------|--------------|--------|-------|
| `debtorAccount` | - | `?DebtorAccount` | ❌ | Account info for specific MCCs (readOnly) |

---

## Descriptive Fields

| Property | Current Type | Correct Type | Status | Notes |
|----------|-------------|--------------|--------|-------|
| `description` | `?string` | `?string` | ✅ | Transaction description (max 255, readOnly) |

---

## Summary of Required Work

### New Enums Needed

- ❌ `TransactionType` (sale, refund, void)
- ❌ `PaymentMethod`
- ❌ `PaymentMethodOrigin`
- ❌ `PaymentMethodQualifier`
- ❌ `ProcessorDirective`
- ❌ `Source`
- ❌ `MarketSegment`
- ❌ `ShopperInteraction`
- ❌ `MarkupRateAnnotation`

### New Data Objects/DTOs Needed

- ❌ `Ach` - ACH payment details
- ❌ `Wallet` - Digital wallet details
- ❌ `Contact` - Shipping/billing contact
- ❌ `ShopperStatement` - Statement descriptor overrides
- ❌ `DebtorAccount` - Debtor account info
- ❌ `Failure` - Failure/error details
- ❌ `CredentialOnFileData` - COF data structure
- ❌ `CredentialOnFile` - COF object
- ❌ `ThreeDSecure` - 3DS authentication data
- ❌ `Surcharge` - Surcharge details

### Type Refinements Needed

- ⚠️ Convert timestamp strings to `DateTimeImmutable`:
  - `createdAt`
  - `modifiedAt`
  - `authorizationExpiresAt`
  - `refundableUntil`
  - `shippingDate`

### Missing Properties to Add

**Financial:**
- `totalRefunded`
- `issuerTotal`
- `tip`
- `salesTax`
- `conversionRate`
- `markupRate`
- `markupRateAnnotation`
- `rateProviderName`

**Payment Method:**
- `paymentMethod`
- `paymentMethodOrigin`
- `paymentMethodQualifier`
- `ach`
- `wallet`
- `threeDSecure`

**Processing:**
- `type`
- `processorDirective`
- `source`
- `marketSegment`
- `isAuthorized`
- `isVoided`
- `isRefunded`
- `isReversed`
- `isCaptured`
- `isSettled`
- `isPartiallyRefunded`
- `failures`

**Resource URLs:**
- `processorAccount`
- `account`
- `forexAdvice`
- `shopper`
- `order`

**Other:**
- `shopperInteraction`
- `shopperStatement`
- `shipTo`
- `billTo`
- `credentialOnFile`
- `debtorAccount`
- `surcharge`

---

## Implementation Recommendations

### Phase 1: Core Types (Priority: High)
1. ✅ Keep existing working properties as-is
2. Create missing enum types (`TransactionType`, etc.)
3. Add missing boolean flags (`isAuthorized`, etc.)
4. Add missing simple string properties

### Phase 2: Financial Properties (Priority: High)
1. Add missing Money properties (`totalRefunded`, `tip`, `salesTax`, etc.)
2. Add rate properties (`conversionRate`, `markupRate`, etc.)

### Phase 3: Timestamp Handling (Priority: Medium)
1. Decide on timestamp strategy:
   - Option A: Keep as strings, validate ISO 8601 format
   - Option B: Convert to `DateTimeImmutable` with proper serialization
   - **Recommendation**: Use `DateTimeImmutable` for type safety

### Phase 4: Complex Objects (Priority: Medium)
1. Create DTOs for complex nested objects:
   - `Contact` (for `shipTo`, `billTo`)
   - `ShopperStatement`
   - `Failure`
   - `ThreeDSecure`
   - `Surcharge`

### Phase 5: Payment Method Details (Priority: Low)
1. Create `Ach` DTO for ACH payments
2. Create `Wallet` DTO for digital wallets
3. Create credential on file structures

### Phase 6: Specialized Types (Priority: Low)
1. `DebtorAccount` (only for specific MCCs)
2. Value objects for email, IP address (if validation needed)

---

## Notes

- Many properties are marked as `readOnly` in the API spec, meaning they only appear in responses
- Some properties are write-only (like sensitive card data)
- The Transaction model should handle both request and response contexts
- Follow the existing pattern in `Card.php` for handling both contexts
- According to `value-objects-list.md`, avoid creating value objects for simple strings without validation needs
