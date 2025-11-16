# Transaction Model Implementation Checklist

This document provides a checklist for implementing all the required types for the Transaction model.

## Enums to Create

### High Priority (Core Transaction Enums)

- [ ] **TransactionType** (`src/Enums/TransactionType.php`)
  - Values: `sale`, `refund`, `void`
  - Used to identify the type of transaction

- [ ] **PaymentMethod** (`src/Enums/PaymentMethod.php`)
  - Values: Check OpenAPI spec for complete list
  - Examples: `card`, `ach`, `applePay`, `googlePay`, `paze`

- [ ] **ProcessorDirective** (`src/Enums/ProcessorDirective.php`)
  - Values: Check OpenAPI spec
  - Indicates how processor should handle the transaction

- [ ] **Source** (`src/Enums/Source.php`)
  - Values: Check OpenAPI spec
  - Transaction source/origin

### Medium Priority

- [ ] **PaymentMethodOrigin** (`src/Enums/PaymentMethodOrigin.php`)
  - Values: Check OpenAPI spec
  - Origin of payment method

- [ ] **PaymentMethodQualifier** (`src/Enums/PaymentMethodQualifier.php`)
  - Values: Check OpenAPI spec
  - Additional payment method qualifiers

- [ ] **MarketSegment** (`src/Enums/MarketSegment.php`)
  - Values: Check OpenAPI spec
  - Market segment for transaction

- [ ] **ShopperInteraction** (`src/Enums/ShopperInteraction.php`)
  - Values: Check OpenAPI spec
  - Type of shopper interaction

- [ ] **MarkupRateAnnotation** (`src/Enums/MarkupRateAnnotation.php`)
  - Values: Check OpenAPI spec
  - Markup rate annotation

## Data Transfer Objects (DTOs) to Create

### High Priority

- [ ] **Failure** (`src/DataObjects/Failure.php`)
  - Properties: `code`, `message`, `field`, etc.
  - Represents transaction failure details
  - Used in array: `failures`

- [ ] **Contact** (`src/DataObjects/Contact.php`)
  - Properties: name, email, phone, address fields
  - Used for: `shipTo`, `billTo`
  - Common structure for contact information

### Medium Priority

- [ ] **ShopperStatement** (`src/DataObjects/ShopperStatement.php`)
  - Properties: Dynamic statement descriptor overrides
  - Check OpenAPI spec for exact structure

- [ ] **Surcharge** (`src/DataObjects/Surcharge.php`)
  - Properties: Surcharge amount and details
  - Check OpenAPI spec for exact structure

- [ ] **ThreeDSecure** (`src/DataObjects/ThreeDSecure.php`)
  - Properties: 3DS authentication data
  - Complex object with many fields

- [ ] **CredentialOnFileData** (`src/DataObjects/CredentialOnFileData.php`)
  - Currently incorrectly typed as `?string`
  - Should be an object/DTO
  - Check OpenAPI spec for structure

- [ ] **CredentialOnFile** (`src/DataObjects/CredentialOnFile.php`)
  - Credential on file structure
  - Check OpenAPI spec

### Lower Priority

- [ ] **Ach** (`src/DataObjects/Ach.php`)
  - ACH payment details
  - Similar to Card DTO pattern

- [ ] **Wallet** (`src/DataObjects/Wallet.php`)
  - Digital wallet details (Apple Pay, Google Pay, Paze)
  - Check OpenAPI spec for structure

- [ ] **DebtorAccount** (`src/DataObjects/DebtorAccount.php`)
  - Required for MCC 6012/6050/6051 merchants
  - Specialized use case

## Transaction Model Updates

### Phase 1: Add Missing Simple Properties

- [ ] Add `type` property (`?TransactionType`)
- [ ] Add `isAuthorized` property (`?bool`)
- [ ] Add `isVoided` property (`?bool`)
- [ ] Add `isRefunded` property (`?bool`)
- [ ] Add `isReversed` property (`?bool`)
- [ ] Add `isCaptured` property (`?bool`)
- [ ] Add `isSettled` property (`?bool`)
- [ ] Add `isPartiallyRefunded` property (`?bool`)
- [ ] Add `processorAccount` property (`?string`)
- [ ] Add `account` property (`?string`)
- [ ] Add `forexAdvice` property (`?string`)
- [ ] Add `shopper` property (`?string`)
- [ ] Add `order` property (`?string`)

### Phase 2: Add Financial Properties

- [ ] Add `totalRefunded` property (`?Money`)
- [ ] Add `issuerTotal` property (`?Money`)
- [ ] Add `tip` property (`?Money`)
- [ ] Add `salesTax` property (`?Money`)
- [ ] Add `conversionRate` property (`?string`)
- [ ] Add `markupRate` property (`?string`)
- [ ] Add `markupRateAnnotation` property (`?MarkupRateAnnotation`)
- [ ] Add `rateProviderName` property (`?string`)

### Phase 3: Add Enum Properties

- [ ] Add `paymentMethod` property (`?PaymentMethod`)
- [ ] Add `paymentMethodOrigin` property (`?PaymentMethodOrigin`)
- [ ] Add `paymentMethodQualifier` property (`?PaymentMethodQualifier`)
- [ ] Add `processorDirective` property (`?ProcessorDirective`)
- [ ] Add `source` property (`?Source`)
- [ ] Add `marketSegment` property (`?MarketSegment`)
- [ ] Add `shopperInteraction` property (`?ShopperInteraction`)

### Phase 4: Add Complex Object Properties

- [ ] Add `failures` property (`?array<Failure>`)
- [ ] Add `shopperStatement` property (`?ShopperStatement`)
- [ ] Add `shipTo` property (`?Contact`)
- [ ] Add `billTo` property (`?Contact`)
- [ ] Add `surcharge` property (`?Surcharge`)
- [ ] Add `threeDSecure` property (`?ThreeDSecure`)
- [ ] Add `ach` property (`?Ach`)
- [ ] Add `wallet` property (`?Wallet`)
- [ ] Add `debtorAccount` property (`?DebtorAccount`)

### Phase 5: Update Existing Properties

- [ ] Convert `credentialOnFileData` from `?string` to `?CredentialOnFileData`
- [ ] Add `credentialOnFile` property (`?CredentialOnFile`)

### Phase 6: Timestamp Handling Decision

**Decision Required:** How to handle timestamps?

**Option A: Keep as strings**
- ✅ Simple, no conversion needed
- ✅ Direct mapping to API
- ❌ Less type-safe
- ❌ No date manipulation methods

**Option B: Use DateTimeImmutable**
- ✅ Type-safe
- ✅ Built-in date methods
- ❌ Requires conversion in fromArray/toArray
- ❌ More complex

**Recommendation:** Use `DateTimeImmutable` for better type safety

If using DateTimeImmutable:
- [ ] Update `createdAt` to `?DateTimeImmutable`
- [ ] Update `modifiedAt` to `?DateTimeImmutable`
- [ ] Update `authorizationExpiresAt` to `?DateTimeImmutable`
- [ ] Update `refundableUntil` to `?DateTimeImmutable`
- [ ] Update `shippingDate` to `?DateTimeImmutable`
- [ ] Add conversion logic in `fromArray()` method
- [ ] Add conversion logic in `toArray()` method

## Testing Requirements

For each new type created, ensure:

- [ ] Unit test file created in `tests/Unit/`
- [ ] Test `fromArray()` method
- [ ] Test `toArray()` method
- [ ] Test validation logic
- [ ] Test edge cases
- [ ] Test null values
- [ ] Test invalid values (should throw exceptions)

## Documentation Requirements

- [ ] Update PHPDoc comments with proper types
- [ ] Document readOnly vs writeOnly properties
- [ ] Document which properties are for requests vs responses
- [ ] Add examples in docblocks where helpful

## Progress Tracking

### Current Status
- **Enums Created:** 1/9 (TransactionState exists)
- **DTOs Created:** 0/10
- **Properties Updated:** ~25/70+

### Next Steps
1. Start with high-priority enums (TransactionType, PaymentMethod)
2. Create Failure DTO (needed for errors)
3. Create Contact DTO (needed for shipping/billing)
4. Gradually add missing properties to Transaction
5. Update tests as properties are added
