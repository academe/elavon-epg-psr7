<?php

declare(strict_types=1);

/**
 * Demo: Payment Form
 *
 * Simple form to initiate a 3DS payment.
 * Run with: php -S localhost:8000 -t demo
 */

$config = require __DIR__ . '/config.php';

// Get the base path for this script (handles both /demo/ and root deployments)
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/';
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
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h1 { color: #333; font-size: 24px; }
        h2 { color: #555; font-size: 18px; margin-top: 30px; }
        .card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
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
        .test-cards {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .test-cards h2 {
            margin-top: 0;
            margin-bottom: 15px;
        }
        .test-cards table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .test-cards th, .test-cards td {
            text-align: left;
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
        }
        .test-cards th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
        }
        .test-cards code {
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Monaco', 'Consolas', monospace;
            font-size: 13px;
        }
        .test-cards code.copyable {
            cursor: pointer;
            transition: background 0.2s;
        }
        .test-cards code.copyable:hover {
            background: #e0e0e0;
        }
        .test-cards code.copied {
            background: #d4edda;
        }
        .test-cards .note {
            margin-top: 15px;
            padding: 10px;
            background: #fff3cd;
            border-radius: 4px;
            font-size: 13px;
            color: #856404;
        }
        .nav-links {
            margin-bottom: 20px;
        }
        .nav-links a {
            display: inline-block;
            padding: 10px 16px;
            background: white;
            color: #0066cc;
            text-decoration: none;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            font-weight: 500;
        }
        .nav-links a:hover {
            background: #f0f0f0;
        }
    </style>
</head>
<body>
    <h1>Elavon 3DS Payment Demo</h1>

    <div class="nav-links">
        <a href="<?= htmlspecialchars($basePath) ?>orders.php">View Orders</a>
    </div>

    <div class="card">
        <form action="<?= htmlspecialchars($basePath) ?>checkout.php" method="POST">
            <label for="customer_name">Customer Name</label>
            <input type="text" id="customer_name" name="customer_name"
                   value="Test Customer" required maxlength="100">

            <label for="email">Email</label>
            <input type="email" id="email" name="email"
                   value="demo@example.com" required maxlength="254">

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

    <div class="test-cards">
        <h2>Test Cards</h2>
        <p>Use these test card numbers in the Hosted Payment Page:</p>

        <table>
            <tr>
                <th>Card Number</th>
                <th>Type</th>
                <th>3DS Result</th>
            </tr>
            <tr>
                <td><code class="copyable">4000000000001091</code></td>
                <td>Visa</td>
                <td>3DS Challenge (authenticated)</td>
            </tr>
            <tr>
                <td><code class="copyable">4000000000001000</code></td>
                <td>Visa</td>
                <td>3DS Frictionless (authenticated)</td>
            </tr>
            <tr>
                <td><code class="copyable">4000000000001109</code></td>
                <td>Visa</td>
                <td>3DS Not Authenticated</td>
            </tr>
            <tr>
                <td><code class="copyable">4000000000001026</code></td>
                <td>Visa</td>
                <td>3DS Unavailable</td>
            </tr>
            <tr>
                <td><code class="copyable">5100000000000511</code></td>
                <td>Mastercard</td>
                <td>3DS Challenge (authenticated)</td>
            </tr>
            <tr>
                <td><code class="copyable">5100000000000529</code></td>
                <td>Mastercard</td>
                <td>3DS Frictionless (authenticated)</td>
            </tr>
        </table>

        <div class="note">
            <strong>For all test cards:</strong><br>
            Expiry: Any future date (e.g., 12/25)<br>
            CVV: Any 3 digits (e.g., 123)<br>
            3DS Password (if prompted): <code>password</code>
        </div>
    </div>

    <script>
        function copyToClipboard(text, el) {
            // Try modern clipboard API first
            if (navigator.clipboard && window.isSecureContext) {
                return navigator.clipboard.writeText(text);
            }
            // Fallback for non-secure contexts
            var textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-9999px';
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
            } catch (err) {
                console.error('Copy failed:', err);
            }
            document.body.removeChild(textArea);
            return Promise.resolve();
        }

        document.querySelectorAll('code.copyable').forEach(function(el) {
            el.title = 'Click to copy';
            el.addEventListener('click', function() {
                var text = this.textContent;
                var element = this;
                copyToClipboard(text, element).then(function() {
                    element.classList.add('copied');
                    element.textContent = 'Copied!';
                    setTimeout(function() {
                        element.textContent = text;
                        element.classList.remove('copied');
                    }, 1000);
                });
            });
        });
    </script>
</body>
</html>
