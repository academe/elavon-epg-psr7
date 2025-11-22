<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Integration;

use Academe\Elavon\Epg\Psr7\Enums\TransactionState;
use Academe\Elavon\Epg\Psr7\Messages\Request\Transaction\CreateTransactionRequest;
use Academe\Elavon\Epg\Psr7\Messages\Response\Transaction\TransactionResponse;
use Academe\Elavon\Epg\Psr7\Support\ElavonApiRequest;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for Elavon Payment Gateway.
 *
 * These tests make real API calls to the Elavon UAT/sandbox environment.
 * Credentials should be configured in a .env file.
 *
 * @group integration
 */
class OpayoIntegrationTest extends TestCase
{
    private Client $httpClient;
    private string $merchantAlias;
    private string $apiSecret;
    private string $baseUri;

    protected function setUp(): void
    {
        parent::setUp();

        // Load environment variables from .env file if it exists
        $this->loadEnv();

        // Get credentials from environment
        // ELAVON_API_SECRET is preferred (required for transaction operations)
        // ELAVON_API_KEY is supported for backwards compatibility
        $this->merchantAlias = getenv('ELAVON_MERCHANT_ALIAS') ?: '';
        $this->apiSecret = getenv('ELAVON_API_SECRET') ?: getenv('ELAVON_API_KEY') ?: '';
        $this->baseUri = getenv('ELAVON_BASE_URI') ?: 'https://uat.api.converge.eu.elavonaws.com';

        // Skip test if credentials are not configured
        if (empty($this->merchantAlias) || empty($this->apiSecret)) {
            $this->markTestSkipped(
                'Integration tests require ELAVON_MERCHANT_ALIAS and ELAVON_API_SECRET (or ELAVON_API_KEY) to be set in .env file'
            );
        }

        // Create HTTP client without auth (we'll use ElavonApiRequest decorator)
        $this->httpClient = new Client([
            'base_uri' => $this->baseUri,
            'timeout' => 30,
            'http_errors' => false, // Don't throw exceptions on HTTP errors
        ]);
    }

    public function test_createTransaction_withValidCard_returnsAuthorized(): void
    {
        // Arrange - Create a transaction request with test card
        $request = new CreateTransactionRequest(
            transaction: [
                'total' => [
                    'amount' => '10.00',
                    'currencyCode' => 'USD',
                ],
                'card' => [
                    'number' => '4111111111111111', // Test card - successful
                    'securityCode' => '123',
                    'expirationMonth' => 12,
                    'expirationYear' => 2025,
                    'holderName' => 'Test Cardholder',
                ],
                'description' => 'Integration test transaction',
                'customReference' => 'TEST-' . time(),
            ],
            baseUri: $this->baseUri,
        );

        // Act - Build request, add Elavon API headers and authentication, then send
        $psr7Request = $request->build();
        $elavonRequest = ElavonApiRequest::create($psr7Request)
            ->withBaseUri($this->baseUri)
            ->withAuthentication($this->merchantAlias, $this->apiSecret);

        // Dump the headers for debugging.
        // foreach ($elavonRequest->getHeaders() as $name => $values) {
        //     echo $name . ': ' . implode(', ', $values) . "\n";
        // }

        $psr7Response = $this->httpClient->send($elavonRequest);

        // Parse the response
        $response = TransactionResponse::fromPsr7Response($psr7Response);

        // Assert - Check for errors first
        if ($response->hasError()) {
            $error = $response->getError();

            $this->fail(
                sprintf(
                    "API returned error (HTTP %d): %s\nError code: %s\nFull response: %s",
                    $response->getStatusCode(),
                    $error->getMessage(),
                    $error->getCode(),
                    (string) $psr7Response->getBody()
                )
            );
        }

        // Verify the response
        $this->assertTrue(
            $response->isSuccessful(),
            sprintf(
                'Expected successful response (2xx), got %d',
                $response->getStatusCode()
            )
        );

        $transaction = $response->getTransaction();
        $this->assertNotNull($transaction, 'Transaction should not be null for successful response');

        // Verify transaction properties
        $this->assertNotNull($transaction->id, 'Transaction ID should be set');
        $this->assertNotEmpty($transaction->id, 'Transaction ID should not be empty');

        // Verify transaction state (could be AUTHORIZED or CAPTURED depending on merchant config)
        $this->assertContains(
            $transaction->state,
            [TransactionState::AUTHORIZED, TransactionState::CAPTURED],
            'Transaction should be either AUTHORIZED or CAPTURED'
        );

        // Verify amount
        $this->assertSame('10.00', $transaction->total->amount);
        $this->assertSame('USD', $transaction->total->currency->value);

        // Verify card response data
        $this->assertNotNull($transaction->card, 'Card data should be present in response');
        $this->assertSame('1111', $transaction->card->last4);

        // Verify timestamp
        $this->assertNotNull($transaction->createdAt, 'CreatedAt timestamp should be set');

        // Output transaction details for debugging
        echo "\n";
        echo "Transaction ID: {$transaction->id}\n";
        echo "State: {$transaction->state->value}\n";
        echo "Amount: {$transaction->total->amount} {$transaction->total->currency->value}\n";
        echo "Card Last 4: {$transaction->card->last4}\n";
        if ($transaction->card->scheme !== null) {
            echo "Card Scheme: {$transaction->card->scheme->value}\n";
        }
        echo "Created At: {$transaction->createdAt}\n";
    }

    public function test_createTransaction_withDeclinedCard_returnsDeclined(): void
    {
        // Arrange - Create a transaction request with test declined card
        $request = new CreateTransactionRequest(
            transaction: [
                'total' => [
                    'amount' => '10.00',
                    'currencyCode' => 'USD',
                ],
                'card' => [
                    'number' => '4000000000000002', // Test card - declined
                    'securityCode' => '123',
                    'expirationMonth' => 12,
                    'expirationYear' => 2025,
                    'holderName' => 'Test Cardholder',
                ],
                'description' => 'Integration test - declined transaction',
                'customReference' => 'TEST-DECLINED-' . time(),
            ],
            baseUri: $this->baseUri,
        );

        // Act - Build request, add Elavon API headers and authentication, then send
        $psr7Request = $request->build();
        $elavonRequest = ElavonApiRequest::create($psr7Request)
            ->withBaseUri($this->baseUri)
            ->withAuthentication($this->merchantAlias, $this->apiSecret);
        $psr7Response = $this->httpClient->send($elavonRequest);

        // Parse the response
        $response = TransactionResponse::fromPsr7Response($psr7Response);

        // Assert - Check for errors first (authentication, etc.)
        if ($response->hasError()) {
            $error = $response->getError();
            $this->fail(
                sprintf(
                    "API returned error (HTTP %d): %s\nError code: %s\nFull response: %s",
                    $response->getStatusCode(),
                    $error->getMessage(),
                    $error->getCode(),
                    (string) $psr7Response->getBody()
                )
            );
        }

        // The HTTP request might still be successful even if the transaction is declined
        // We care about the transaction state, not necessarily the HTTP status
        $transaction = $response->getTransaction();
        $this->assertNotNull($transaction, 'Transaction should not be null even for declined card');

        $this->assertSame(
            TransactionState::DECLINED,
            $transaction->state,
            'Transaction should be DECLINED for test card 4000000000000002'
        );

        // Output transaction details for debugging
        echo "\n";
        echo "Transaction ID: {$transaction->id}\n";
        echo "State: {$transaction->state->value}\n";
        echo "Amount: {$transaction->total->amount} {$transaction->total->currency->value}\n";
    }

    /**
     * Load environment variables from .env file.
     */
    private function loadEnv(): void
    {
        $envFile = __DIR__ . '/../../.env';

        if (!file_exists($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            // Skip comments
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            // Parse KEY=VALUE
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Don't override existing environment variables
                if (!getenv($key)) {
                    putenv("{$key}={$value}");
                }
            }
        }
    }
}
