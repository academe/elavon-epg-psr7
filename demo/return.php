<?php

declare(strict_types=1);

/**
 * Demo: Payment Return
 *
 * Handles return from Elavon's Hosted Payment Page after payment/3DS completion.
 * Fetches the payment session from the API to verify the result.
 */

session_start();

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

use Academe\Elavon\Epg\Psr7\Messages\Request\PaymentSession\RetrievePaymentSessionRequest;
use Academe\Elavon\Epg\Psr7\Messages\Request\Transaction\RetrieveTransactionRequest;
use Academe\Elavon\Epg\Psr7\Messages\Response\PaymentSession\PaymentSessionResponse;
use Academe\Elavon\Epg\Psr7\Messages\Response\Transaction\TransactionResponse;
use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
use GuzzleHttp\Client;

$config = require __DIR__ . '/config.php';

// Security: Get session ID from PHP session (stored during checkout)
// This prevents users from faking the result by manipulating URL parameters
$sessionId = $_SESSION['payment_session_id'] ?? null;
$sessionCreated = $_SESSION['payment_session_created'] ?? 0;

// Clear the session data after use
unset($_SESSION['payment_session_id'], $_SESSION['payment_session_created']);

// Capture return parameters from HPP (for display purposes)
$returnParams = $_GET;

// Initialize result variables
$paymentSession = null;
$transaction = null;
$verificationError = null;
$isVerified = false;

// Verify the payment by fetching the session from the API
if ($sessionId) {
    // Check if session is not too old (1 hour max)
    if (time() - $sessionCreated > 3600) {
        $verificationError = 'Payment session has expired. Please try again.';
    } else {
        try {
            $httpClient = new Client(['http_errors' => false]);
            $apiFactory = ElavonApiFactory::configure()
                ->withBaseUri($config['base_uri'])
                ->withAuthentication($config['merchant_alias'], $config['api_secret']);

            // Fetch the payment session from the API
            $request = (new RetrievePaymentSessionRequest($sessionId))->build();
            $request = $apiFactory->apply($request);

            $response = $httpClient->send($request);
            $sessionResponse = PaymentSessionResponse::fromPsr7Response($response);

            if ($sessionResponse->hasError()) {
                $verificationError = 'Failed to verify payment: ' . $sessionResponse->getError()->getMessage();
            } else {
                $paymentSession = $sessionResponse->getPaymentSession();
                $isVerified = true;

                // If a transaction exists, fetch its details
                $transactionUrl = $paymentSession->transaction ?? null;
                if ($transactionUrl) {
                    // Extract transaction ID from URL (e.g., "/transactions/abc123" -> "abc123")
                    $transactionId = basename($transactionUrl);

                    $txnRequest = (new RetrieveTransactionRequest($transactionId))->build();
                    $txnRequest = $apiFactory->apply($txnRequest);

                    $txnResponse = $httpClient->send($txnRequest);
                    $transactionResponse = TransactionResponse::fromPsr7Response($txnResponse);

                    if (!$transactionResponse->hasError()) {
                        $transaction = $transactionResponse->getTransaction();
                    }
                }
            }
        } catch (Throwable $e) {
            $verificationError = 'Verification error: ' . $e->getMessage();
        }
    }
} else {
    $verificationError = 'No payment session found. This could indicate a direct access attempt or session expiry.';
}

// Determine the status
$status = 'unknown';
$statusClass = 'pending';
$statusMessage = 'Payment status could not be determined.';

if ($isVerified && $paymentSession) {
    if ($transaction) {
        // Use actual transaction state
        $transactionState = $transaction->transactionState ?? 'unknown';

        if (in_array($transactionState, ['AUTHORIZED', 'CAPTURED', 'SETTLED'])) {
            $status = 'completed';
            $statusClass = 'success';
            $statusMessage = "Payment {$transactionState} and verified from the gateway.";
        } elseif ($transactionState === 'DECLINED') {
            $status = 'declined';
            $statusClass = 'error';
            $statusMessage = 'Payment was declined by the processor.';
        } else {
            $status = 'pending';
            $statusClass = 'pending';
            $statusMessage = "Transaction state: {$transactionState}";
        }
    } elseif ($paymentSession->transaction) {
        // Transaction URL exists but we couldn't fetch details
        $status = 'completed';
        $statusClass = 'success';
        $statusMessage = 'Payment completed and verified from the gateway.';
    } else {
        $status = 'pending';
        $statusClass = 'pending';
        $statusMessage = 'Payment session exists but no transaction was created yet.';
    }
}

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
            max-width: 700px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h1 { font-size: 24px; }
        h2 { font-size: 18px; color: #555; margin-top: 25px; }
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
        .error { border-left: 4px solid #dc3545; }
        .error h1 { color: #dc3545; }
        .verified {
            display: inline-block;
            padding: 4px 10px;
            background: #d4edda;
            color: #155724;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .not-verified {
            display: inline-block;
            padding: 4px 10px;
            background: #f8d7da;
            color: #721c24;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .detail-row {
            display: flex;
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }
        .detail-label {
            width: 180px;
            font-weight: 500;
            color: #555;
        }
        .detail-value {
            flex: 1;
            word-break: break-all;
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
            white-space: pre-wrap;
            word-break: break-all;
        }
        a.button {
            display: inline-block;
            padding: 12px 24px;
            background: #0066cc;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        a.button:hover { background: #0052a3; }
        .security-note {
            margin-top: 20px;
            padding: 15px;
            background: #e8f4fd;
            border-radius: 4px;
            font-size: 13px;
            color: #0066cc;
        }
        .security-note strong { display: block; margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="card <?= $statusClass ?>">
        <h1>
            <?php if ($status === 'completed'): ?>
                Payment Completed
            <?php elseif ($verificationError): ?>
                Verification Failed
            <?php else: ?>
                Payment Pending
            <?php endif; ?>
        </h1>

        <p><?= htmlspecialchars($statusMessage) ?></p>

        <p>
            <?php if ($isVerified): ?>
                <span class="verified">Verified from Gateway</span>
            <?php else: ?>
                <span class="not-verified">Not Verified</span>
            <?php endif; ?>
        </p>

        <?php if ($verificationError): ?>
            <p style="color: #721c24;"><strong>Error:</strong> <?= htmlspecialchars($verificationError) ?></p>
        <?php endif; ?>
    </div>

    <?php if ($isVerified && $paymentSession): ?>
    <div class="card">
        <h2>Payment Session Details</h2>

        <div class="detail-row">
            <div class="detail-label">Session ID</div>
            <div class="detail-value"><?= htmlspecialchars($paymentSession->id ?? 'N/A') ?></div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Order</div>
            <div class="detail-value"><?= htmlspecialchars($paymentSession->order ?? 'N/A') ?></div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Transaction</div>
            <div class="detail-value"><?= htmlspecialchars($paymentSession->transaction ?? 'None') ?></div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Email</div>
            <div class="detail-value"><?= htmlspecialchars($paymentSession->shopperEmailAddress ?? 'N/A') ?></div>
        </div>

        <?php if ($paymentSession->billTo): ?>
        <div class="detail-row">
            <div class="detail-label">Customer Name</div>
            <div class="detail-value"><?= htmlspecialchars($paymentSession->billTo->fullName ?? 'N/A') ?></div>
        </div>
        <?php endif; ?>

        <?php if ($transaction): ?>
        <h2>Transaction Details</h2>
        <div class="detail-row">
            <div class="detail-label">Transaction ID</div>
            <div class="detail-value"><?= htmlspecialchars($transaction->id ?? 'N/A') ?></div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Status</div>
            <div class="detail-value"><?= htmlspecialchars($transaction->transactionState ?? 'N/A') ?></div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Type</div>
            <div class="detail-value"><?= htmlspecialchars($transaction->transactionType ?? 'N/A') ?></div>
        </div>
        <?php if ($transaction->total): ?>
        <div class="detail-row">
            <div class="detail-label">Amount</div>
            <div class="detail-value"><?= htmlspecialchars($transaction->total->amount ?? 'N/A') ?> <?= htmlspecialchars($transaction->total->currencyCode ?? '') ?></div>
        </div>
        <?php endif; ?>
        <?php if ($transaction->card): ?>
        <div class="detail-row">
            <div class="detail-label">Card</div>
            <div class="detail-value"><?= htmlspecialchars($transaction->card->maskedPan ?? 'N/A') ?> (<?= htmlspecialchars($transaction->card->cardBrand ?? 'N/A') ?>)</div>
        </div>
        <?php endif; ?>
        <div class="detail-row">
            <div class="detail-label">Approval Code</div>
            <div class="detail-value"><?= htmlspecialchars($transaction->approvalCode ?? 'N/A') ?></div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Response Code</div>
            <div class="detail-value"><?= htmlspecialchars($transaction->responseCode ?? 'N/A') ?></div>
        </div>
        <?php endif; ?>

        <?php if ($paymentSession->threeDSecure): ?>
        <h2>3D Secure Results</h2>
        <div class="detail-row">
            <div class="detail-label">Status</div>
            <div class="detail-value"><?= htmlspecialchars($paymentSession->threeDSecure->transactionStatus ?? 'N/A') ?></div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Protocol Version</div>
            <div class="detail-value"><?= htmlspecialchars($paymentSession->threeDSecure->protocolVersion ?? 'N/A') ?></div>
        </div>
        <div class="detail-row">
            <div class="detail-label">ECI</div>
            <div class="detail-value"><?= htmlspecialchars($paymentSession->threeDSecure->electronicCommerceIndicator ?? 'N/A') ?></div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($returnParams)): ?>
    <div class="card">
        <div class="params">
            <h3>Return URL Parameters (from HPP)</h3>
            <pre><?= htmlspecialchars(json_encode($returnParams, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></pre>
        </div>
    </div>
    <?php endif; ?>

    <div class="security-note">
        <strong>Security Note</strong>
        This demo verifies the payment result by fetching the PaymentSession directly from
        the Elavon API using credentials stored server-side. This prevents users from faking
        the payment result by manipulating URL parameters. In production, always verify
        payment status from your server, never trust client-side data alone.
    </div>

    <p><a href="index.php" class="button">Make another payment</a></p>
</body>
</html>
