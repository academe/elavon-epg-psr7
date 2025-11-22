<?php

declare(strict_types=1);

/**
 * Demo: Checkout
 *
 * Creates an Order and PaymentSession, then redirects to Elavon's HPP.
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

use Academe\Elavon\Epg\Psr7\Dtos\Contact;
use Academe\Elavon\Epg\Psr7\Dtos\Order;
use Academe\Elavon\Epg\Psr7\Dtos\PaymentSession;
use Academe\Elavon\Epg\Psr7\Messages\Request\Order\CreateOrderRequest;
use Academe\Elavon\Epg\Psr7\Messages\Request\PaymentSession\CreatePaymentSessionRequest;
use Academe\Elavon\Epg\Psr7\Messages\Response\Order\OrderResponse;
use Academe\Elavon\Epg\Psr7\Messages\Response\PaymentSession\PaymentSessionResponse;
use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
use GuzzleHttp\Client;

$config = require __DIR__ . '/config.php';

// Validate input
$customerName = filter_input(INPUT_POST, 'customer_name', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'Test Customer';
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?: 'demo@example.com';
$amount = filter_input(INPUT_POST, 'amount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$currency = filter_input(INPUT_POST, 'currency', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'EUR';
$description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'Test payment';

if (!$amount || (float)$amount <= 0) {
    die('Invalid amount');
}

// Set up HTTP client and API factory
$httpClient = new Client(['http_errors' => false]);
$apiFactory = ElavonApiFactory::configure()
    ->withBaseUri($config['base_uri'])
    ->withAuthentication($config['merchant_alias'], $config['api_secret']);

try {
    // Step 1: Create an Order
    $order = new Order(
        total: ['amount' => $amount, 'currencyCode' => $currency],
        description: $description,
        shopperEmailAddress: $email,
    );

    $orderRequest = (new CreateOrderRequest($order))->build();
    $orderRequest = $apiFactory->apply($orderRequest);

    $orderHttpResponse = $httpClient->send($orderRequest);
    $orderResponse = OrderResponse::fromPsr7Response($orderHttpResponse);

    if ($orderResponse->hasError()) {
        throw new RuntimeException('Order creation failed: ' . $orderResponse->getError()->getMessage());
    }

    $orderData = $orderResponse->getOrder();
    $orderHref = $orderData->href ?? null;

    if (!$orderHref) {
        throw new RuntimeException('Order created but no href returned');
    }

    // Step 2: Create PaymentSession with 3DS enabled
    // Include billTo with customer name - this pre-fills the cardholder name on HPP
    $paymentSession = new PaymentSession(
        order: $orderHref,
        returnUrl: $config['demo_url'] . '/return.php',
        cancelUrl: $config['demo_url'] . '/cancel.php',
        doThreeDSecure: true,
        doCreateTransaction: true,
        shopperEmailAddress: $email,
        billTo: new Contact(fullName: $customerName, email: $email),
    );

    $sessionRequest = (new CreatePaymentSessionRequest($paymentSession))->build();
    $sessionRequest = $apiFactory->apply($sessionRequest);

    $sessionHttpResponse = $httpClient->send($sessionRequest);
    $sessionResponse = PaymentSessionResponse::fromPsr7Response($sessionHttpResponse);

    if ($sessionResponse->hasError()) {
        throw new RuntimeException('PaymentSession creation failed: ' . $sessionResponse->getError()->getMessage());
    }

    $sessionData = $sessionResponse->getPaymentSession();
    $hppUrl = $sessionData->url ?? null;
    $sessionId = $sessionData->id ?? null;

    if (!$hppUrl) {
        // Debug: show what we got
        echo '<h2>Debug: PaymentSession Response</h2>';
        echo '<pre>' . htmlspecialchars(json_encode($sessionData->toData(), JSON_PRETTY_PRINT)) . '</pre>';
        die('PaymentSession created but no HPP URL returned');
    }

    // Store session ID in PHP session for verification on return
    // This prevents users from faking the result by manipulating URL parameters
    session_start();
    $_SESSION['payment_session_id'] = $sessionId;
    $_SESSION['payment_session_created'] = time();

    // Step 3: Redirect to Hosted Payment Page
    header('Location: ' . $hppUrl);
    exit;

} catch (Throwable $e) {
    // Show error page
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Checkout Error</title>
        <style>
            body { font-family: sans-serif; max-width: 600px; margin: 40px auto; padding: 20px; }
            .error { background: #fee; padding: 20px; border-radius: 8px; border: 1px solid #fcc; }
            h1 { color: #c00; }
            pre { background: #f5f5f5; padding: 15px; overflow: auto; font-size: 12px; }
            a { color: #0066cc; }
        </style>
    </head>
    <body>
        <div class="error">
            <h1>Checkout Error</h1>
            <p><?= htmlspecialchars($e->getMessage()) ?></p>
            <pre><?= htmlspecialchars($e->getTraceAsString()) ?></pre>
        </div>
        <p><a href="index.php">Back to payment form</a></p>
    </body>
    </html>
    <?php
}
