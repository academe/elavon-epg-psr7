<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\Transaction;

use Academe\Elavon\Epg\Psr7\Dtos\Transaction;
use Academe\Elavon\Epg\Psr7\Enums\Currency;
use Academe\Elavon\Epg\Psr7\Messages\Request\Transaction\CreateTransactionRequest;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Academe\Elavon\Epg\Psr7\ValueObjects\Money;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Tests for CreateTransactionRequest message.
 */
class CreateTransactionRequestTest extends TestCase
{
    public function test_construct_withTransactionArray_createsInstance(): void
    {
        // Arrange
        $transaction = [
            'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
            'card' => [
                'number' => '4111111111111111',
                'securityCode' => '123',
                'expirationMonth' => 12,
                'expirationYear' => 2025,
            ],
        ];

        // Act
        $request = new CreateTransactionRequest(transaction: $transaction);

        // Assert
        $this->assertInstanceOf(CreateTransactionRequest::class, $request);
    }

    public function test_construct_withTransactionObject_createsInstance(): void
    {
        // Arrange
        $transaction = new Transaction(
            total: ['amount' => '99.99', 'currencyCode' => 'USD'],
            card: [
                'number' => '4111111111111111',
                'securityCode' => '123',
                'expirationMonth' => 12,
                'expirationYear' => 2025,
            ],
        );

        // Act
        $request = new CreateTransactionRequest(transaction: $transaction);

        // Assert
        $this->assertInstanceOf(CreateTransactionRequest::class, $request);
    }

    public function test_build_withDefaultFactory_returnsValidRequest(): void
    {
        // Arrange
        $request = new CreateTransactionRequest(
            transaction: [
                'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
                'card' => [
                    'number' => '4111111111111111',
                    'securityCode' => '123',
                    'expirationMonth' => 12,
                    'expirationYear' => 2025,
                ],
            ],
        );

        // Act
        $psr7Request = $request->build();

        // Assert
        $this->assertSame('POST', $psr7Request->getMethod());
        $this->assertSame('https://api.eu.elavonpayments.com/transactions', (string) $psr7Request->getUri());
        $this->assertSame('application/json', $psr7Request->getHeaderLine('Content-Type'));
        $this->assertSame('application/json', $psr7Request->getHeaderLine('Accept'));
    }

    public function test_build_withCustomBaseUri_usesCustomUri(): void
    {
        // Arrange
        $request = new CreateTransactionRequest(
            transaction: [
                'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
                'card' => ['number' => '4111111111111111'],
            ],
            baseUri: 'https://uat.api.converge.eu.elavonaws.com',
        );

        // Act
        $psr7Request = $request->build();

        // Assert
        $this->assertSame('https://uat.api.converge.eu.elavonaws.com/transactions', (string) $psr7Request->getUri());
    }

    public function test_build_bodyContainsSerializedTransaction(): void
    {
        // Arrange
        $request = new CreateTransactionRequest(
            transaction: [
                'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
                'card' => [
                    'number' => '4111111111111111',
                    'securityCode' => '123',
                    'expirationMonth' => 12,
                    'expirationYear' => 2025,
                    'holderName' => 'John Doe',
                ],
                'description' => 'Order #12345',
            ],
        );

        // Act
        $psr7Request = $request->build();
        $body = (string) $psr7Request->getBody();
        $decoded = json_decode($body, true);

        // Assert
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('total', $decoded);
        $this->assertSame('99.99', $decoded['total']['amount']);
        $this->assertSame('USD', $decoded['total']['currencyCode']);
        $this->assertArrayHasKey('card', $decoded);
        $this->assertSame('4111111111111111', $decoded['card']['number']);
        $this->assertSame('123', $decoded['card']['securityCode']);
        $this->assertSame(12, $decoded['card']['expirationMonth']);
        $this->assertSame(2025, $decoded['card']['expirationYear']);
        $this->assertSame('John Doe', $decoded['card']['holderName']);
        $this->assertSame('Order #12345', $decoded['description']);
    }

    public function test_build_withCustomFactories_usesCustomFactories(): void
    {
        // Arrange
        $requestFactory = new Psr17Factory();
        $streamFactory = new Psr17Factory();

        $request = new CreateTransactionRequest(
            transaction: [
                'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
                'card' => ['number' => '4111111111111111'],
            ],
            requestFactory: $requestFactory,
            streamFactory: $streamFactory,
        );

        // Act
        $psr7Request = $request->build();

        // Assert
        $this->assertSame('POST', $psr7Request->getMethod());
        $this->assertSame('application/json', $psr7Request->getHeaderLine('Content-Type'));
    }

    public function test_getTransaction_withTransactionArray_returnsTransactionObject(): void
    {
        // Arrange
        $transactionData = [
            'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
            'card' => [
                'number' => '4111111111111111',
                'securityCode' => '123',
                'expirationMonth' => 12,
                'expirationYear' => 2025,
            ],
        ];

        $request = new CreateTransactionRequest(transaction: $transactionData);

        // Act
        $transaction = $request->getTransaction();

        // Assert
        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertSame('99.99', $transaction->total->amount);
        $this->assertSame(Currency::USD, $transaction->total->currency);
        $this->assertSame('4111111111111111', $transaction->card->number);
    }

    public function test_getTransaction_withTransactionObject_returnsSameObject(): void
    {
        // Arrange
        $originalTransaction = new Transaction(
            total: new Money('99.99', Currency::USD),
            card: ['number' => '4111111111111111'],
        );

        $request = new CreateTransactionRequest(transaction: $originalTransaction);

        // Act
        $transaction = $request->getTransaction();

        // Assert
        $this->assertSame($originalTransaction, $transaction);
    }

    public function test_build_producesValidJson(): void
    {
        // Arrange
        $request = new CreateTransactionRequest(
            transaction: [
                'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
                'card' => ['number' => '4111111111111111'],
            ],
        );

        // Act
        $psr7Request = $request->build();
        $body = (string) $psr7Request->getBody();

        // Assert
        $this->assertJson($body);
    }

    public function test_build_canBeCalledMultipleTimes(): void
    {
        // Arrange
        $request = new CreateTransactionRequest(
            transaction: [
                'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
                'card' => ['number' => '4111111111111111'],
            ],
        );

        // Act
        $psr7Request1 = $request->build();
        $psr7Request2 = $request->build();

        // Assert
        $this->assertSame((string) $psr7Request1->getBody(), (string) $psr7Request2->getBody());
        $this->assertNotSame($psr7Request1, $psr7Request2);
    }

    public function test_build_withMinimalTransaction_succeeds(): void
    {
        // Arrange
        $request = new CreateTransactionRequest(
            transaction: [
                'total' => ['amount' => '1.00', 'currencyCode' => 'USD'],
                'card' => ['number' => '4111111111111111'],
            ],
        );

        // Act
        $psr7Request = $request->build();
        $body = (string) $psr7Request->getBody();
        $decoded = json_decode($body, true);

        // Assert
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('total', $decoded);
        $this->assertSame('1.00', $decoded['total']['amount']);
    }

    public function test_build_doesNotIncludeNullFields(): void
    {
        // Arrange
        $request = new CreateTransactionRequest(
            transaction: [
                'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
                'card' => ['number' => '4111111111111111'],
                'description' => 'Test',
            ],
        );

        // Act
        $psr7Request = $request->build();
        $body = (string) $psr7Request->getBody();
        $decoded = json_decode($body, true);

        // Assert
        $this->assertArrayHasKey('total', $decoded);
        $this->assertArrayHasKey('card', $decoded);
        $this->assertArrayHasKey('description', $decoded);
        $this->assertArrayNotHasKey('id', $decoded);
        $this->assertArrayNotHasKey('state', $decoded);
    }

    public function test_build_withComplexTransaction_serializesCorrectly(): void
    {
        // Arrange
        $request = new CreateTransactionRequest(
            transaction: [
                'total' => ['amount' => '199.99', 'currencyCode' => 'EUR'],
                'card' => [
                    'number' => '5555555555554444',
                    'securityCode' => '999',
                    'expirationMonth' => 6,
                    'expirationYear' => 2026,
                    'holderName' => 'Jane Smith',
                ],
                'description' => 'Premium subscription',
                'customReference' => 'CUST-REF-999',
            ],
        );

        // Act
        $psr7Request = $request->build();
        $body = (string) $psr7Request->getBody();
        $decoded = json_decode($body, true);

        // Assert
        $this->assertSame('199.99', $decoded['total']['amount']);
        $this->assertSame('EUR', $decoded['total']['currencyCode']);
        $this->assertSame('5555555555554444', $decoded['card']['number']);
        $this->assertSame('999', $decoded['card']['securityCode']);
        $this->assertSame(6, $decoded['card']['expirationMonth']);
        $this->assertSame(2026, $decoded['card']['expirationYear']);
        $this->assertSame('Jane Smith', $decoded['card']['holderName']);
        $this->assertSame('Premium subscription', $decoded['description']);
        $this->assertSame('CUST-REF-999', $decoded['customReference']);
    }

    public function test_build_headersAreCaseInsensitive(): void
    {
        // Arrange
        $request = new CreateTransactionRequest(
            transaction: [
                'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
                'card' => ['number' => '4111111111111111'],
            ],
        );

        // Act
        $psr7Request = $request->build();

        // Assert
        $this->assertSame('application/json', $psr7Request->getHeaderLine('content-type'));
        $this->assertSame('application/json', $psr7Request->getHeaderLine('CONTENT-TYPE'));
        $this->assertSame('application/json', $psr7Request->getHeaderLine('Content-Type'));
    }
}