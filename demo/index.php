<?php

declare(strict_types=1);

/**
 * Demo: Payment Form
 *
 * Simple form to initiate a 3DS payment.
 * Run with: php -S localhost:8000
 */

$config = require __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elavon 3DS Payment Demo</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 500px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h1 { color: #333; font-size: 24px; }
        .card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
        }
        input, select {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #0066cc;
        }
        button {
            width: 100%;
            padding: 14px;
            background: #0066cc;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover { background: #0052a3; }
        .info {
            margin-top: 20px;
            padding: 15px;
            background: #e8f4fd;
            border-radius: 4px;
            font-size: 14px;
            color: #0066cc;
        }
        .env-info {
            margin-top: 10px;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>
    <h1>Elavon 3DS Payment Demo</h1>

    <div class="card">
        <form action="checkout.php" method="POST">
            <label for="amount">Amount</label>
            <input type="text" id="amount" name="amount" value="50.00" required
                   pattern="^\d+(\.\d{2})?$" title="Enter amount like 50.00">

            <label for="currency">Currency</label>
            <select id="currency" name="currency">
                <option value="EUR" selected>EUR - Euro</option>
                <option value="GBP">GBP - British Pound</option>
                <option value="USD">USD - US Dollar</option>
            </select>

            <label for="description">Description</label>
            <input type="text" id="description" name="description"
                   value="Test payment with 3DS" required maxlength="255">

            <label for="email">Email (optional)</label>
            <input type="email" id="email" name="email"
                   placeholder="shopper@example.com">

            <button type="submit">Pay with 3DS</button>
        </form>

        <div class="info">
            This demo creates an Order, then a PaymentSession with 3DS enabled,
            and redirects you to Elavon's Hosted Payment Page.
        </div>

        <div class="env-info">
            Environment: <?= htmlspecialchars($config['base_uri']) ?>
        </div>
    </div>
</body>
</html>
