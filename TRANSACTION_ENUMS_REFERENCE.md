# Transaction-Related Enums Reference

This document lists all enum values needed for the Transaction model, extracted from the Elavon Payment Gateway OpenAPI specification.

## Core Transaction Enums

### TransactionType
**File:** `src/Enums/TransactionType.php`
**Description:** Type of transaction
**Status:** ❌ Not implemented

```php
enum TransactionType: string
{
    case SALE = 'sale';
    case REFUND = 'refund';
    case VOID = 'void';
}
```

### TransactionState
**File:** `src/Enums/TransactionState.php`
**Description:** Current state of transaction
**Status:** ✅ Already implemented

Values:
- `declined`
- `authorized`
- `captured`
- `voided`
- `settled`
- `expired`
- `settlementDelayed`
- `rejected`
- `heldForReview`
- `unknown`
- `authorizationPending`

---

## Payment Method Enums

### PaymentMethod
**File:** `src/Enums/PaymentMethod.php`
**Description:** Payment method type
**Status:** ❌ Not implemented

```php
enum PaymentMethod: string
{
    case CARD = 'Card';
    case BLIK = 'BLIK';
    case ACH = 'ACH';
}
```

**Note:** Values are case-sensitive. API uses PascalCase for Card, UPPERCASE for BLIK and ACH.

### PaymentMethodOrigin
**File:** `src/Enums/PaymentMethodOrigin.php`
**Description:** Origin of the payment method (may differ from payment method if using a wallet)
**Status:** ❌ Not implemented

```php
enum PaymentMethodOrigin: string
{
    case CARD = 'Card';
    case APPLE_PAY = 'Apple Pay';
    case GOOGLE_PAY = 'Google Pay';
    case PAZE = 'Paze';
    case BLIK = 'BLIK';
    case POLISH_BANK_TRANSFER = 'Polish Bank Transfer';
    case ACH = 'ACH';
    case UNKNOWN_WALLET = 'Unknown Wallet';
}
```

**Note:** These values contain spaces and mixed case. Use exact API values.

### PaymentMethodQualifier
**File:** `src/Enums/PaymentMethodQualifier.php`
**Description:** Payment method qualifier (credit vs debit)
**Status:** ❌ Not implemented

```php
enum PaymentMethodQualifier: string
{
    case CREDIT = 'credit';
    case DEBIT = 'debit';
}
```

---

## Processing Enums

### ProcessorDirective
**File:** `src/Enums/ProcessorDirective.php`
**Description:** Directive for how processor should handle the transaction
**Status:** ❌ Not implemented

```php
enum ProcessorDirective: string
{
    case NONE = 'none';
    case REVERSAL = 'reversal';
}
```

### Source
**File:** `src/Enums/Source.php`
**Description:** How the transaction was submitted (defaults to 'directApiCall')
**Status:** ❌ Not implemented

```php
enum Source: string
{
    case DIRECT_API_CALL = 'directApiCall';
    case HPP_SUBMIT_REDIRECT = 'hppSubmitRedirect';
    case HPP_IFRAME_LIGHTBOX = 'hppIframeLightbox';
    case HPP_IFRAME_EMBEDDED = 'hppIframeEmbedded';
    case HPP_SDK = 'hppSdk';
    case VIRTUAL_TERMINAL = 'virtualTerminal';
    case GATEWAY_RECURRING = 'gatewayRecurring';
    case PAY_BY_LINK = 'payByLink';
    case MONITORING = 'monitoring';
    case HPP_FIELDS = 'hppFields';
    case PHYSICAL_TERMINAL = 'physicalTerminal';
    case UNKNOWN = 'unknown';
}
```

---

## Business Context Enums

### MarketSegment
**File:** `src/Enums/MarketSegment.php`
**Description:** Market segment for the transaction
**Status:** ❌ Not implemented

```php
enum MarketSegment: string
{
    case RETAIL = 'retail';
    case RESTAURANT = 'restaurant';
}
```

### ShopperInteraction
**File:** `src/Enums/ShopperInteraction.php`
**Description:** Type of interaction with the shopper
**Status:** ❌ Not implemented

```php
enum ShopperInteraction: string
{
    case ECOMMERCE = 'ecommerce';           // Ecommerce interaction
    case MAIL_ORDER = 'mailOrder';           // Mail order
    case TELEPHONE_ORDER = 'telephoneOrder'; // Telephone order
    case MERCHANT_INITIATED = 'merchantInitiated'; // Merchant-initiated, no shopper involvement
    case IN_PERSON = 'inPerson';             // In-person with physical payment method
}
```

---

## Financial Enums

### MarkupRateAnnotation
**File:** `src/Enums/MarkupRateAnnotation.php`
**Description:** Markup rate annotation for currency conversion
**Status:** ❌ Not implemented

```php
enum MarkupRateAnnotation: string
{
    case NONE = 'none';
    case ABOVE_ECB = 'aboveEcb';  // Above European Central Bank rate
    case BELOW_ECB = 'belowEcb';  // Below European Central Bank rate
}
```

---

## Implementation Priority

### High Priority (Core Functionality)
1. **TransactionType** - Essential for differentiating transaction types
2. **PaymentMethod** - Core to payment processing
3. **Source** - Important for tracking transaction origin

### Medium Priority (Enhanced Functionality)
4. **PaymentMethodOrigin** - Important for wallet payments
5. **ProcessorDirective** - Processing control
6. **ShopperInteraction** - Business context
7. **MarketSegment** - Business context

### Lower Priority (Optional Features)
8. **PaymentMethodQualifier** - Credit/debit distinction
9. **MarkupRateAnnotation** - Currency conversion metadata

---

## Implementation Template

Each enum should follow this template:

```php
<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Enums;

/**
 * [Description from OpenAPI spec]
 *
 * [Additional context if needed]
 */
enum EnumName: string
{
    case VALUE_NAME = 'apiValue';

    // Add more cases...
}
```

---

## Testing Template

Each enum should have a corresponding test:

```php
<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Enums;

use Academe\Elavon\Epg\Psr7\Enums\EnumName;
use PHPUnit\Framework\TestCase;

class EnumNameTest extends TestCase
{
    public function test_enumHasExpectedValues(): void
    {
        $this->assertSame('apiValue', EnumName::VALUE_NAME->value);
        // Test all values...
    }

    public function test_tryFromReturnsCorrectCase(): void
    {
        $this->assertSame(EnumName::VALUE_NAME, EnumName::tryFrom('apiValue'));
    }

    public function test_tryFromReturnsNullForInvalidValue(): void
    {
        $this->assertNull(EnumName::tryFrom('invalid'));
    }
}
```

---

## Notes

### Case Sensitivity
Be aware that enum values are case-sensitive:
- `PaymentMethod` uses PascalCase for `Card` but uppercase for `BLIK` and `ACH`
- `PaymentMethodOrigin` includes spaces in values like `'Apple Pay'`
- Most other enums use camelCase

### Backwards Compatibility
When adding these enums:
1. Ensure `tryFrom()` is used in `fromArray()` methods to handle invalid values gracefully
2. Throw `InvalidArgumentException` when invalid values are encountered
3. Document readOnly vs request properties

### API Version
These enums are based on API version 2025-10-01. Future API versions may add new values.
