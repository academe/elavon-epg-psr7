# DTO Array Parameters

This document lists all array parameters in DTOs and their element types.

## Typed Object Arrays

These arrays contain DTO objects and are normalized in the constructor.

| DTO | Property | Element Type | Notes |
|-----|----------|--------------|-------|
| Account | `$processorAccounts` | `ProcessorAccount` | |
| ErrorResponse | `$failures` | `ErrorDetail` | |
| Order | `$items` | `OrderItem` | Max 64 items |
| PaymentSession | `$previousTransactions` | `Transaction` | |
| ProcessorAccount | `$pinlessDebit` | `PinlessDebitCardScheme` | |
| TotalAdjustment | `$failures` | `Failure` | |
| Transaction | `$failures` | `Failure` | |

## Typed Enum Arrays

These arrays contain enum values and are normalized using `normalizeEnumArray()`.

| DTO | Property | Element Type | Notes |
|-----|----------|--------------|-------|
| Merchant | `$regions` | `Region` | NA, EU |
| PaymentSession | `$allowedPaymentMethods` | `PaymentMethod` | |
| PaymentSession | `$allowedPaymentMethodOrigins` | `PaymentMethodOrigin` | |
| ProcessorAccount | `$supportedCardBrands` | `CardBrand` | |
| ProcessorAccount | `$supportedPaymentMethods` | `PaymentMethod` | |
| ProcessorAccount | `$supportedPaymentMethodOrigins` | `PaymentMethodOrigin` | |

## String Arrays

These arrays contain simple string values.

| DTO | Property | Element Type | Notes |
|-----|----------|--------------|-------|
| PaymentLink | `$status` | `string` | Status values: active, completed, cancelled, expired |
| PaymentMethodLink | `$status` | `string` | Status values |

## Custom Fields (Key-Value Arrays)

All `$customFields` properties are `array<string, string>` (associative arrays with string keys and values).

| DTO |
|-----|
| Account |
| ApplePayPayment |
| ForexAdvice |
| GooglePayPayment |
| HostedAchPayment |
| HostedCard |
| HsmCard |
| ManualBatch |
| Order |
| PaymentLink |
| PaymentMethodLink |
| PaymentMethodSession |
| PaymentSession |
| PazePayment |
| Plan |
| RefundSurchargeAdvice |
| Shopper |
| StoredAchPayment |
| StoredCard |
| Subscription |
| SurchargeAdvice |
| TotalAdjustment |
