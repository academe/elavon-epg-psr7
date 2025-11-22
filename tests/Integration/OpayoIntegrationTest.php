<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Integration;

use Academe\Elavon\Epg\Psr7\Enums\TransactionState;
use Academe\Elavon\Epg\Psr7\Messages\Request\Transaction\CreateTransactionRequest;
use Academe\Elavon\Epg\Psr7\Messages\Response\Transaction\TransactionResponse;
use Academe\Elavon\Epg\Psr7\Support\ElavonApiFactory;
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
        // Integration tests require the secret API key (sk_...) for server-side operations
        // The public API key (pk_...) is only for client-side hosted card operations
        $this->merchantAlias = getenv('ELAVON_MERCHANT_ALIAS') ?: '';
        $this->apiSecret = getenv('ELAVON_API_SECRET') ?: '';
        $this->baseUri = getenv('ELAVON_BASE_URI') ?: 'https://uat.api.converge.eu.elavonaws.com';

        // Skip test if credentials are not configured
        if (empty($this->merchantAlias) || empty($this->apiSecret)) {
            $this->markTestSkipped(
                'Integration tests require ELAVON_MERCHANT_ALIAS and ELAVON_API_SECRET to be set in .env file'
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
        // Note: This test may fail if the merchant account requires 3D Secure authentication.
        // The failure will include: "3dsEnforcedOnEcommerceSales"
        // To resolve: Configure the merchant account to allow non-3DS transactions for testing,
        // or implement 3DS flow in a separate test.

        // Use expiration date 2 years in the future
        $expirationYear = (int) date('Y') + 2;
        $expirationMonth = (int) date('n');

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
                    'expirationMonth' => $expirationMonth,
                    'expirationYear' => $expirationYear,
                    'holderName' => 'Test Cardholder',
                ],
                'description' => 'Integration test transaction',
                'customReference' => 'TEST-' . bin2hex(random_bytes(8)),
            ],
            baseUri: $this->baseUri,
        );

        // Act - Build request, add Elavon API headers and authentication, then send
        $psr7Request = $request->build();
        
        $requestDecorator = ElavonApiFactory::configure()
            ->withBaseUri($this->baseUri)
            ->withAuthentication($this->merchantAlias, $this->apiSecret);

        $decoratedRequest = $requestDecorator->decorate($psr7Request);

        $psr7Response = $this->httpClient->send($decoratedRequest);

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
        // Note: If merchant account requires 3DS, transaction will be DECLINED with "3dsEnforcedOnEcommerceSales"
        $validStates = [TransactionState::AUTHORIZED, TransactionState::CAPTURED];

        if ($transaction->state === TransactionState::DECLINED) {
            // Check if declined due to 3DS requirement - this is expected for some merchant configurations
            $failureCodes = array_map(fn($f) => $f->code ?? '', $transaction->failures ?? []);

            if (in_array('3dsEnforcedOnEcommerceSales', $failureCodes, true)) {
                $this->markTestSkipped(
                    'Transaction declined due to 3DS requirement. ' .
                    'Configure merchant account to allow non-3DS transactions for testing, ' .
                    'or implement 3DS authentication flow.'
                );
            }
        }

        $this->assertContains(
            $transaction->state,
            $validStates,
            sprintf(
                'Transaction should be AUTHORIZED or CAPTURED, got %s. Failures: %s',
                $transaction->state?->value ?? 'null',
                $transaction->failures
                    ? json_encode(array_map(fn($f) => $f->toData(), $transaction->failures))
                    : 'none'
            )
        );

        // Verify amount
        $this->assertSame('10.00', $transaction->total->amount);
        $this->assertSame('USD', $transaction->total->currency->value);

        // Verify card response data
        $this->assertNotNull($transaction->card, 'Card data should be present in response');
        $this->assertSame('1111', $transaction->card->last4);

        // Verify timestamp
        $this->assertNotNull($transaction->createdAt, 'CreatedAt timestamp should be set');
    }

    public function test_createTransaction_withDeclinedCard_returnsDeclined(): void
    {
        // Use expiration date 2 years in the future
        $expirationYear = (int) date('Y') + 2;
        $expirationMonth = (int) date('n');

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
                    'expirationMonth' => $expirationMonth,
                    'expirationYear' => $expirationYear,
                    'holderName' => 'Test Cardholder',
                ],
                'description' => 'Integration test - declined transaction',
                'customReference' => 'TEST-DECLINED-' . bin2hex(random_bytes(8)),
            ],
            baseUri: $this->baseUri,
        );

        // Act - Build request, add Elavon API headers and authentication, then send
        $psr7Request = $request->build();
        $decoratedRequest = ElavonApiFactory::configure()
            ->withBaseUri($this->baseUri)
            ->withAuthentication($this->merchantAlias, $this->apiSecret)
            ->decorate($psr7Request);
        $psr7Response = $this->httpClient->send($decoratedRequest);

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
