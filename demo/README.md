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

1. **index.php** - Simple payment form (amount, currency, description)
2. **checkout.php** - Creates Order + PaymentSession with 3DS, redirects to HPP
3. **Elavon HPP** - User enters card details, completes 3DS challenge
4. **return.php** - Shows payment result after completion
5. **cancel.php** - Shows cancellation message if user cancels

## Test Cards

For UAT/sandbox testing, use Elavon's test card numbers. Check your Elavon documentation for current test cards that trigger different 3DS scenarios:

- 3DS challenge required
- 3DS frictionless flow
- 3DS authentication failed

## Notes

- This demo uses `doThreeDSecure: true` to enforce 3DS authentication
- The `returnUrl` and `cancelUrl` point back to this demo server
- In production, you would verify the transaction status on return
