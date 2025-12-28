<?php

declare(strict_types=1);

/**
 * Demo: Order List
 *
 * Displays a paginated list of orders from the Elavon API.
 * Run with: php -S localhost:8000 -t demo
 */

// Autoload - works from both demo/ and project root
$autoloadPaths = [
    __DIR__ . '/../vendor/autoload.php',  // Running from demo/
    __DIR__ . '/vendor/autoload.php',      // If symlinked
];
foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        require $path;
        break;
    }
}
if (!class_exists(\Academe\Elavon\Epg\Psr7\Dtos\Order::class)) {
    die('Autoloader not found. Run: composer install');
}

use Academe\Elavon\Epg\Psr7\Dtos\QueryParams;
use Academe\Elavon\Epg\Psr7\Messages\Request\Order\RetrieveOrderListRequest;
use Academe\Elavon\Epg\Psr7\Messages\Response\Order\OrderListResponse;
use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
use GuzzleHttp\Client;

$config = require __DIR__ . '/config.php';

// Get the base path for this script
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/';

// Pagination: use cursor from query string if provided
$cursor = filter_input(INPUT_GET, 'cursor', FILTER_SANITIZE_URL) ?: null;
$limit = 10;

// Track page number for display (stored in session for simplicity)
session_start();
if ($cursor === null) {
    $_SESSION['order_page'] = 1;
    $_SESSION['order_cursors'] = []; // Stack of previous cursors for back navigation
} else {
    $_SESSION['order_page'] = ($_SESSION['order_page'] ?? 1);
}
$pageNumber = $_SESSION['order_page'] ?? 1;

// Set up HTTP client and API factory
$httpClient = new Client(['http_errors' => false]);
$apiFactory = ElavonApiFactory::configure()
    ->withRegion($config['region'])
    ->withEnvironment($config['environment'])
    ->withAuthentication($config['merchant_alias'], $config['api_secret']);

$orders = null;
$error = null;
$nextPage = null;
$nextCursor = null;
$prevCursor = null;

try {
    // Build query params with pagination
    $queryParams = QueryParams::create()->withLimit($limit);

    // If we have a cursor (pageToken), add it
    if ($cursor) {
        $queryParams = $queryParams->withPageToken($cursor);
    }

    $request = (new RetrieveOrderListRequest($queryParams))->build();
    $request = $apiFactory->apply($request);

    $httpResponse = $httpClient->send($request);
    $response = OrderListResponse::fromPsr7Response($httpResponse);

    if ($response->isSuccessful()) {
        $orders = $response->orders;
        $nextCursor = $response->nextPageToken;

        // Handle back navigation using session cursor stack
        if ($cursor !== null && isset($_GET['action']) && $_GET['action'] === 'next') {
            // Going forward: push current cursor to stack
            $_SESSION['order_cursors'][] = $cursor;
            $_SESSION['order_page']++;
        }

        // Get previous cursor from stack (if any)
        if (!empty($_SESSION['order_cursors'])) {
            $prevCursor = end($_SESSION['order_cursors']);
        }

        $pageNumber = $_SESSION['order_page'] ?? 1;
    } else {
        $error = $response->error?->getMessage() ?? 'Unknown error';
    }
} catch (Throwable $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order List - Elavon Demo</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h1 { color: #333; font-size: 24px; }
        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .error {
            background: #fee;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #fcc;
            color: #c00;
        }
        .empty {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .order-id {
            font-family: 'Monaco', 'Consolas', monospace;
            font-size: 12px;
            color: #666;
        }
        .amount {
            font-weight: 500;
        }
        .status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-open { background: #e3f2fd; color: #1565c0; }
        .status-closed { background: #e8f5e9; color: #2e7d32; }
        .status-cancelled { background: #ffebee; color: #c62828; }
        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .pagination a, .pagination span {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
        }
        .pagination a {
            background: #0066cc;
            color: white;
        }
        .pagination a:hover {
            background: #0052a3;
        }
        .pagination .disabled {
            background: #ddd;
            color: #999;
            cursor: not-allowed;
        }
        .pagination .info {
            color: #666;
            font-size: 14px;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #0066cc;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .env-info {
            margin-top: 10px;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>
    <a href="<?= htmlspecialchars($basePath) ?>index.php" class="back-link">&larr; Back to Payment Form</a>

    <h1>Order List</h1>

    <?php if ($error): ?>
        <div class="error">
            <strong>Error:</strong> <?= htmlspecialchars($error) ?>
        </div>
    <?php elseif (empty($orders)): ?>
        <div class="card empty">
            <p>No orders found.</p>
            <p>Create a payment using the <a href="<?= htmlspecialchars($basePath) ?>index.php">payment form</a> to see orders here.</p>
        </div>
    <?php else: ?>
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td class="order-id"><?= htmlspecialchars($order->id ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($order->description ?? '-') ?></td>
                        <td class="amount">
                            <?php if ($order->total): ?>
                                <?= htmlspecialchars(number_format((float)($order->total->getAmount() / 100), 2)) ?>
                                <?= htmlspecialchars($order->total->getCurrency()->getCode()) ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $status = $order->state->value ?? 'unknown';
                            $statusClass = match (strtolower($status)) {
                                'open' => 'status-open',
                                'closed' => 'status-closed',
                                'cancelled' => 'status-cancelled',
                                default => '',
                            };
                            ?>
                            <span class="status <?= $statusClass ?>"><?= htmlspecialchars(ucfirst($status)) ?></span>
                        </td>
                        <td>
                            <?= $order->createdAt ? htmlspecialchars($order->createdAt->format('Y-m-d H:i:s')) : '-' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="pagination">
                <?php if ($pageNumber > 1): ?>
                    <a href="?">First</a>
                <?php else: ?>
                    <span class="disabled">First</span>
                <?php endif; ?>

                <span class="info">
                    Page <?= $pageNumber ?>
                </span>

                <?php if ($nextCursor): ?>
                    <a href="?cursor=<?= urlencode($nextCursor) ?>&amp;action=next">Next &rarr;</a>
                <?php else: ?>
                    <span class="disabled">Next &rarr;</span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="env-info">
        Environment: <?= htmlspecialchars($config['base_uri']) ?>
    </div>
</body>
</html>