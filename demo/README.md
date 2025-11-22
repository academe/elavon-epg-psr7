# Elavon 3DS Payment Demo

A minimal demo showing the 3D Secure payment flow using Elavon's Hosted Payment Page.

## Quick Start

1. **Configure credentials** - Copy `.env.example` to `.env` in the project root and add your Elavon UAT credentials:
   ```
   ELAVON_MERCHANT_ALIAS=your_merchant_alias
   ELAVON_API_SECRET=sk_your_secret_key
   ELAVON_BASE_URI=https://uat.api.converge.eu.elavonaws.com
   ```

2. **Install dependencies** (if not already done):
   ```bash
   composer install
   ```

3. **Start the PHP built-in server** from the project root:
   ```bash
   php -S localhost:8000 -t demo
   ```

4. **Open in browser**: http://localhost:8000

## Flow

1. **index.php** - Payment form with customer name, email, amount, and test card reference
2. **checkout.php** - Creates Order + PaymentSession with 3DS, stores session ID, redirects to HPP
3. **Elavon HPP** - User enters card details, completes 3DS challenge
4. **return.php** - Fetches PaymentSession from API to verify result (security)
5. **cancel.php** - Shows cancellation message if user cancels

## Test Cards

| Card Number | Type | 3DS Result |
|-------------|------|------------|
| `4000000000001091` | Visa | 3DS Challenge (authenticated) |
| `4000000000001000` | Visa | 3DS Frictionless (authenticated) |
| `4000000000001109` | Visa | 3DS Not Authenticated |
| `4000000000001026` | Visa | 3DS Unavailable |
| `5100000000000511` | Mastercard | 3DS Challenge (authenticated) |
| `5100000000000529` | Mastercard | 3DS Frictionless (authenticated) |

**For all test cards:**
- Expiry: Any future date (e.g., 12/25)
- CVV: Any 3 digits (e.g., 123)
- 3DS Password (if prompted): `password`

## Security Model

This demo demonstrates secure payment verification:

1. **Session ID stored server-side**: The PaymentSession ID is stored in the PHP session during checkout, not passed via URL parameters
2. **API verification on return**: When the user returns from HPP, we fetch the PaymentSession directly from the Elavon API to verify the result
3. **Prevents result manipulation**: Users cannot fake a successful payment by manipulating URL parameters

In production, you should:
- Always verify payment status from your server
- Never trust client-side data alone
- Store transaction references in your database
- Implement idempotency to handle duplicate callbacks

## Features Demonstrated

- Creating Orders with `CreateOrderRequest`
- Creating PaymentSessions with `CreatePaymentSessionRequest`
- Using `billTo` Contact for customer name
- Enabling 3DS with `doThreeDSecure: true`
- Retrieving PaymentSessions with `RetrievePaymentSessionRequest`
- Using `ElavonApiFactory` for authentication and base URI
