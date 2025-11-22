<?php

declare(strict_types=1);

/**
 * Demo: Payment Cancelled
 *
 * Handles cancellation from Elavon's Hosted Payment Page.
 */

$config = require __DIR__ . '/config.php';

// Capture any cancel parameters
$cancelParams = $_GET;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Cancelled</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h1 { font-size: 24px; color: #666; }
        .card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid #6c757d;
            margin-bottom: 20px;
        }
        .params {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
        }
        .params h3 {
            margin-top: 0;
            font-size: 14px;
            color: #666;
        }
        .params pre {
            margin: 0;
            font-size: 12px;
        }
        a {
            display: inline-block;
            padding: 12px 24px;
            background: #0066cc;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        a:hover { background: #0052a3; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Payment Cancelled</h1>
        <p>You cancelled the payment on the Hosted Payment Page.</p>
        <p>No payment has been taken from your account.</p>

        <?php if (!empty($cancelParams)): ?>
            <div class="params">
                <h3>Cancel Parameters</h3>
                <pre><?= htmlspecialchars(json_encode($cancelParams, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></pre>
            </div>
        <?php endif; ?>
    </div>

    <a href="index.php">Try again</a>
</body>
</html>
