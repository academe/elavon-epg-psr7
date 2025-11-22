<?php

declare(strict_types=1);

/**
 * Demo: Payment Return
 *
 * Handles return from Elavon's Hosted Payment Page after payment/3DS completion.
 */

// Autoload
$autoloadPaths = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/vendor/autoload.php',
];
foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        require $path;
        break;
    }
}

$config = require __DIR__ . '/config.php';

// Capture all return parameters from HPP
$returnParams = $_GET;

// Common parameters that might be returned
$transactionId = $returnParams['transactionId'] ?? null;
$paymentSessionId = $returnParams['paymentSessionId'] ?? null;
$status = $returnParams['status'] ?? null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Result</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h1 { font-size: 24px; }
        .card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .success { border-left: 4px solid #28a745; }
        .success h1 { color: #28a745; }
        .pending { border-left: 4px solid #ffc107; }
        .pending h1 { color: #856404; }
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
            white-space: pre-wrap;
            word-break: break-all;
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
        .note {
            margin-top: 20px;
            padding: 15px;
            background: #e8f4fd;
            border-radius: 4px;
            font-size: 14px;
            color: #0066cc;
        }
    </style>
</head>
<body>
    <div class="card <?= $status === 'success' ? 'success' : 'pending' ?>">
        <h1>
            <?php if ($status === 'success'): ?>
                Payment Completed
            <?php else: ?>
                Payment Return
            <?php endif; ?>
        </h1>

        <p>You have returned from the Elavon Hosted Payment Page.</p>

        <?php if ($transactionId): ?>
            <p><strong>Transaction ID:</strong> <?= htmlspecialchars($transactionId) ?></p>
        <?php endif; ?>

        <?php if ($paymentSessionId): ?>
            <p><strong>Payment Session ID:</strong> <?= htmlspecialchars($paymentSessionId) ?></p>
        <?php endif; ?>

        <?php if (!empty($returnParams)): ?>
            <div class="params">
                <h3>Return Parameters</h3>
                <pre><?= htmlspecialchars(json_encode($returnParams, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></pre>
            </div>
        <?php endif; ?>

        <div class="note">
            <strong>Note:</strong> In a production application, you would:
            <ol>
                <li>Retrieve the PaymentSession to check its status</li>
                <li>Verify the transaction details match your order</li>
                <li>Check 3DS authentication results</li>
                <li>Update your order/database accordingly</li>
            </ol>
        </div>
    </div>

    <a href="index.php">Make another payment</a>
</body>
</html>
