<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\Transaction;

use Academe\Elavon\Epg\Psr7\Dtos\Transaction;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\Transaction\CreateTransactionRequest;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Money\Money;
use PHPUnit\Framework\TestCase;

/**
 * Tests for CreateTransactionRequest message.
 */
class CreateTransactionRequestTest extends TestCase
{
    public function test_fromData_withMissingTransactionKey_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Missing required key 'transaction' in data");

        CreateTransactionRequest::fromData([]);
    }

    public function test_construct_withTransactionObject_createsInstance(): void
    {
        // Arrange
        $transaction = Transaction::fromData([
            'total' => Money::USD(9999),
            'card' => [
                'number' => '4111111111111111',
                'securityCode' => '123',
                'expirationMonth' => 12,
                'expirationYear' => 2025,
            ],
        ]);

        // Act
        $request = new CreateTransactionRequest(transaction: $transaction);

        // Assert
        $this->assertInstanceOf(CreateTransactionRequest::class, $request);
        $this->assertSame($transaction, $request->transaction);
    }

    public function test_fromData_withTransactionArray_createsInstance(): void
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

        // Act
        $request = CreateTransactionRequest::fromData(['transaction' => $transactionData]);

        // Assert
        $this->assertInstanceOf(CreateTransactionRequest::class, $request);
        $this->assertInstanceOf(Transaction::class, $request->transaction);
    }

    public function test_fromData_withTransactionObject_createsInstance(): void
    {
        // Arrange
        $transaction = Transaction::fromData([
            'total' => Money::USD(9999),
            'card' => ['number' => '4111111111111111'],
        ]);

        // Act
        $request = CreateTransactionRequest::fromData(['transaction' => $transaction]);

        // Assert
        $this->assertInstanceOf(CreateTransactionRequest::class, $request);
        $this->assertSame($transaction, $request->transaction);
    }

    public function test_build_withDefaultFactory_returnsValidRequest(): void
    {
        // Arrange
        $request = CreateTransactionRequest::fromData([
            'transaction' => [
                'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
                'card' => [
                    'number' => '4111111111111111',
                    'securityCode' => '123',
                    'expirationMonth' => 12,
                    'expirationYear' => 2025,
                ],
            ],
        ]);

        // Act
        $psr7Request = $request->build();

        // Assert
        $this->assertSame('POST', $psr7Request->getMethod());
        $this->assertSame('/transactions', (string) $psr7Request->getUri());
    }

    public function test_build_bodyContainsSerializedTransaction(): void
    {
        // Arrange
        $request = CreateTransactionRequest::fromData([
            'transaction' => [
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
        ]);

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

        $request = CreateTransactionRequest::fromData([
            'transaction' => [
                'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
                'card' => ['number' => '4111111111111111'],
            ],
        ])
        ->withRequestFactory($requestFactory)
        ->withStreamFactory($streamFactory);

        // Act
        $psr7Request = $request->build();

        // Assert
        $this->assertSame('POST', $psr7Request->getMethod());
    }

    public function test_transaction_property_returnsTransactionObject(): void
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

        $request = CreateTransactionRequest::fromData(['transaction' => $transactionData]);

        // Assert
        $this->assertInstanceOf(Transaction::class, $request->transaction);
        $this->assertSame('9999', $request->transaction->total->getAmount());
        $this->assertSame('USD', $request->transaction->total->getCurrency()->getCode());
        $this->assertSame('4111111111111111', $request->transaction->card->number);
    }

    public function test_transaction_property_withTransactionObject_returnsSameObject(): void
    {
        // Arrange
        $originalTransaction = Transaction::fromData([
            'total' => Money::USD(9999), // 99.99 in cents
            'card' => ['number' => '4111111111111111'],
        ]);

        $request = new CreateTransactionRequest(transaction: $originalTransaction);

        // Assert
        $this->assertSame($originalTransaction, $request->transaction);
    }

    public function test_build_producesValidJson(): void
    {
        // Arrange
        $request = CreateTransactionRequest::fromData([
            'transaction' => [
                'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
                'card' => ['number' => '4111111111111111'],
            ],
        ]);

        // Act
        $psr7Request = $request->build();
        $body = (string) $psr7Request->getBody();

        // Assert
        $this->assertJson($body);
    }

    public function test_build_canBeCalledMultipleTimes(): void
    {
        // Arrange
        $request = CreateTransactionRequest::fromData([
            'transaction' => [
                'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
                'card' => ['number' => '4111111111111111'],
            ],
        ]);

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
        $request = CreateTransactionRequest::fromData([
            'transaction' => [
                'total' => ['amount' => '1.00', 'currencyCode' => 'USD'],
                'card' => ['number' => '4111111111111111'],
            ],
        ]);

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
        $request = CreateTransactionRequest::fromData([
            'transaction' => [
                'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
                'card' => ['number' => '4111111111111111'],
                'description' => 'Test',
            ],
        ]);

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
        $request = CreateTransactionRequest::fromData([
            'transaction' => [
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
        ]);

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
}
