<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\DataObjects;

use Academe\Elavon\Epg\Psr7\DataObjects\Card;
use Academe\Elavon\Epg\Psr7\DataObjects\Transaction;
use Academe\Elavon\Epg\Psr7\Enums\CardScheme;
use Academe\Elavon\Epg\Psr7\Enums\Currency;
use Academe\Elavon\Epg\Psr7\Enums\TransactionState;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Transaction DTO.
 */
class TransactionTest extends TestCase
{
    public function test_construct_withMoneyObject_createsInstance(): void
    {
        // Arrange
        $money = new Money('99.99', Currency::USD);

        // Act
        $transaction = new Transaction(total: $money);

        // Assert
        $this->assertSame($money, $transaction->total);
        $this->assertNull($transaction->card);
        $this->assertNull($transaction->id);
        $this->assertNull($transaction->state);
        $this->assertNull($transaction->description);
        $this->assertNull($transaction->customReference);
        $this->assertNull($transaction->createdAt);
    }

    public function test_construct_withTotalArray_createsInstance(): void
    {
        // Arrange & Act
        $transaction = new Transaction(
            total: ['amount' => '99.99', 'currencyCode' => 'USD']
        );

        // Assert
        $this->assertInstanceOf(Money::class, $transaction->total);
        $this->assertSame('99.99', $transaction->total->amount);
        $this->assertSame(Currency::USD, $transaction->total->currency);
    }

    public function test_construct_withCardObject_createsInstance(): void
    {
        // Arrange
        $card = new Card(
            number: '4111111111111111',
            securityCode: '123',
            expirationMonth: 12,
            expirationYear: 2025,
        );

        // Act
        $transaction = new Transaction(
            total: ['amount' => '99.99', 'currencyCode' => 'USD'],
            card: $card,
        );

        // Assert
        $this->assertSame($card, $transaction->card);
    }

    public function test_construct_withCardArray_createsInstance(): void
    {
        // Arrange & Act
        $transaction = new Transaction(
            total: ['amount' => '99.99', 'currencyCode' => 'USD'],
            card: [
                'number' => '4111111111111111',
                'securityCode' => '123',
                'expirationMonth' => 12,
                'expirationYear' => 2025,
            ],
        );

        // Assert
        $this->assertInstanceOf(Card::class, $transaction->card);
        $this->assertSame('4111111111111111', $transaction->card->number);
    }

    public function test_construct_withAllFields_createsInstance(): void
    {
        // Arrange & Act
        $transaction = new Transaction(
            total: ['amount' => '99.99', 'currencyCode' => 'USD'],
            card: ['number' => '4111111111111111', 'securityCode' => '123', 'expirationMonth' => 12, 'expirationYear' => 2025],
            id: 'txn_123',
            state: TransactionState::AUTHORIZED,
            description: 'Order #12345',
            customReference: 'REF-12345',
            createdAt: '2025-11-13T10:00:00Z',
        );

        // Assert
        $this->assertSame('99.99', $transaction->total->amount);
        $this->assertSame(Currency::USD, $transaction->total->currency);
        $this->assertInstanceOf(Card::class, $transaction->card);
        $this->assertSame('txn_123', $transaction->id);
        $this->assertSame(TransactionState::AUTHORIZED, $transaction->state);
        $this->assertSame('Order #12345', $transaction->description);
        $this->assertSame('REF-12345', $transaction->customReference);
        $this->assertSame('2025-11-13T10:00:00Z', $transaction->createdAt);
    }

    public function test_construct_withNegativeTotal_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Transaction total must be positive');

        // Act
        new Transaction(
            total: ['amount' => '-10.00', 'currencyCode' => 'USD']
        );
    }

    public function test_construct_withZeroTotal_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Transaction total must be positive');

        // Act
        new Transaction(
            total: ['amount' => '0.00', 'currencyCode' => 'USD']
        );
    }

    public function test_fromArray_withMinimalData_createsInstance(): void
    {
        // Arrange
        $data = [
            'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
        ];

        // Act
        $transaction = Transaction::fromArray($data);

        // Assert
        $this->assertSame('99.99', $transaction->total->amount);
        $this->assertSame(Currency::USD, $transaction->total->currency);
        $this->assertNull($transaction->card);
        $this->assertNull($transaction->id);
        $this->assertNull($transaction->state);
    }

    public function test_fromArray_withFullData_createsInstance(): void
    {
        // Arrange
        $data = [
            'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
            'card' => [
                'number' => '4111111111111111',
                'securityCode' => '123',
                'expirationMonth' => 12,
                'expirationYear' => 2025,
                'holderName' => 'John Doe',
            ],
            'id' => 'txn_123',
            'state' => 'authorized',
            'description' => 'Order #12345',
            'customReference' => 'REF-12345',
            'createdAt' => '2025-11-13T10:00:00Z',
        ];

        // Act
        $transaction = Transaction::fromArray($data);

        // Assert
        $this->assertSame('99.99', $transaction->total->amount);
        $this->assertSame('4111111111111111', $transaction->card->number);
        $this->assertSame('txn_123', $transaction->id);
        $this->assertSame(TransactionState::AUTHORIZED, $transaction->state);
        $this->assertSame('Order #12345', $transaction->description);
        $this->assertSame('REF-12345', $transaction->customReference);
        $this->assertSame('2025-11-13T10:00:00Z', $transaction->createdAt);
    }

    public function test_fromArray_withMissingTotal_throwsException(): void
    {
        // Arrange
        $data = [
            'card' => ['number' => '4111111111111111'],
        ];

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field: total');

        // Act
        Transaction::fromArray($data);
    }

    public function test_fromArray_withInvalidState_throwsException(): void
    {
        // Arrange
        $data = [
            'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
            'state' => 'invalid_state',
        ];

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid transaction state: invalid_state');

        // Act
        Transaction::fromArray($data);
    }

    public function test_toArray_withMinimalData_returnsArray(): void
    {
        // Arrange
        $transaction = new Transaction(
            total: ['amount' => '99.99', 'currencyCode' => 'USD']
        );

        // Act
        $array = $transaction->toArray();

        // Assert
        $this->assertSame([
            'total' => [
                'amount' => '99.99',
                'currencyCode' => 'USD',
            ],
        ], $array);
    }

    public function test_toArray_withFullData_returnsArray(): void
    {
        // Arrange
        $transaction = new Transaction(
            total: ['amount' => '99.99', 'currencyCode' => 'USD'],
            card: [
                'number' => '4111111111111111',
                'securityCode' => '123',
                'expirationMonth' => 12,
                'expirationYear' => 2025,
                'holderName' => 'John Doe',
            ],
            id: 'txn_123',
            state: TransactionState::AUTHORIZED,
            description: 'Order #12345',
            customReference: 'REF-12345',
            createdAt: '2025-11-13T10:00:00Z',
        );

        // Act
        $array = $transaction->toArray();

        // Assert
        $this->assertSame([
            'total' => [
                'amount' => '99.99',
                'currencyCode' => 'USD',
            ],
            'card' => [
                'number' => '4111111111111111',
                'securityCode' => '123',
                'expirationMonth' => 12,
                'expirationYear' => 2025,
                'holderName' => 'John Doe',
            ],
            'id' => 'txn_123',
            'state' => 'authorized',
            'description' => 'Order #12345',
            'customReference' => 'REF-12345',
            'createdAt' => '2025-11-13T10:00:00Z',
        ], $array);
    }

    public function test_toArray_onlyIncludesNonNullValues(): void
    {
        // Arrange
        $transaction = new Transaction(
            total: ['amount' => '99.99', 'currencyCode' => 'USD'],
            description: 'Test',
        );

        // Act
        $array = $transaction->toArray();

        // Assert
        $this->assertArrayHasKey('total', $array);
        $this->assertArrayHasKey('description', $array);
        $this->assertArrayNotHasKey('card', $array);
        $this->assertArrayNotHasKey('id', $array);
        $this->assertArrayNotHasKey('state', $array);
        $this->assertArrayNotHasKey('customReference', $array);
        $this->assertArrayNotHasKey('createdAt', $array);
    }

    public function test_roundTrip_fromArrayToArray_preservesData(): void
    {
        // Arrange
        $originalData = [
            'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
            'card' => [
                'number' => '4111111111111111',
                'securityCode' => '123',
                'expirationMonth' => 12,
                'expirationYear' => 2025,
            ],
            'description' => 'Test transaction',
        ];

        // Act
        $transaction = Transaction::fromArray($originalData);
        $resultData = $transaction->toArray();

        // Assert
        $this->assertSame($originalData, $resultData);
    }

    public function test_properties_totalIsReadonly(): void
    {
        // Arrange
        $transaction = new Transaction(
            total: ['amount' => '99.99', 'currencyCode' => 'USD']
        );

        // Act & Assert
        $reflection = new \ReflectionProperty($transaction, 'total');
        $this->assertTrue($reflection->isReadOnly());
    }

    public function test_mixedConstruction_moneyObjectAndCardArray(): void
    {
        // Arrange
        $money = new Money('99.99', Currency::USD);

        // Act
        $transaction = new Transaction(
            total: $money,
            card: [
                'number' => '4111111111111111',
                'securityCode' => '123',
                'expirationMonth' => 12,
                'expirationYear' => 2025,
            ],
        );

        // Assert
        $this->assertSame($money, $transaction->total);
        $this->assertInstanceOf(Card::class, $transaction->card);
        $this->assertSame('4111111111111111', $transaction->card->number);
    }

    public function test_mixedConstruction_totalArrayAndCardObject(): void
    {
        // Arrange
        $card = new Card(
            number: '4111111111111111',
            securityCode: '123',
            expirationMonth: 12,
            expirationYear: 2025,
        );

        // Act
        $transaction = new Transaction(
            total: ['amount' => '99.99', 'currencyCode' => 'USD'],
            card: $card,
        );

        // Assert
        $this->assertInstanceOf(Money::class, $transaction->total);
        $this->assertSame($card, $transaction->card);
    }

    public function test_construct_withResponseData_createsInstance(): void
    {
        // Arrange & Act
        $transaction = new Transaction(
            total: ['amount' => '99.99', 'currencyCode' => 'USD'],
            card: [
                'last4' => '1111',
                'bin' => '411111',
                'scheme' => 'Visa',
            ],
            id: 'txn_123',
            state: TransactionState::CAPTURED,
            createdAt: '2025-11-13T10:00:00Z',
        );

        // Assert
        $this->assertSame('txn_123', $transaction->id);
        $this->assertSame(TransactionState::CAPTURED, $transaction->state);
        $this->assertSame('1111', $transaction->card->last4);
        $this->assertSame('411111', $transaction->card->bin);
        $this->assertSame(CardScheme::VISA, $transaction->card->scheme);
    }
}