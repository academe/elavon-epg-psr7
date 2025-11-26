<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\Card;
use Academe\Elavon\Epg\Psr7\Dtos\Transaction;
use Academe\Elavon\Epg\Psr7\Enums\CardScheme;
use Academe\Elavon\Epg\Psr7\Enums\TransactionState;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\ValueObjects\IpAddress;
use Academe\Elavon\Epg\Psr7\ValueObjects\LanguageTag;
use Academe\Elavon\Epg\Psr7\ValueObjects\TimeZone;
use Money\Money;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Transaction DTO.
 */
class TransactionTest extends TestCase
{
    public function test_construct_withMoneyObject_createsInstance(): void
    {
        // Arrange
        $money = Money::USD(9999); // 99.99 in cents

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
            total: Money::USD(9999)
        );

        // Assert
        $this->assertInstanceOf(Money::class, $transaction->total);
        $this->assertSame('9999', $transaction->total->getAmount());
        $this->assertSame('USD', $transaction->total->getCurrency()->getCode());
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
            total: Money::USD(9999),
            card: $card,
        );

        // Assert
        $this->assertSame($card, $transaction->card);
    }

    public function test_construct_withCardArray_createsInstance(): void
    {
        // Arrange & Act
        $transaction = new Transaction(
            total: Money::USD(9999),
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
            total: Money::USD(9999),
            card: ['number' => '4111111111111111', 'securityCode' => '123', 'expirationMonth' => 12, 'expirationYear' => 2025],
            id: 'txn_123',
            state: TransactionState::AUTHORIZED,
            description: 'Order #12345',
            customReference: 'REF-12345',
            createdAt: '2025-11-13T10:00:00Z',
        );

        // Assert
        $this->assertSame('9999', $transaction->total->getAmount());
        $this->assertSame('USD', $transaction->total->getCurrency()->getCode());
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
            total: Money::USD(-1000)
        );
    }

    public function test_construct_withZeroTotal_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Transaction total must be positive');

        // Act
        new Transaction(
            total: Money::USD(0)
        );
    }

    public function test_fromArray_withMinimalData_createsInstance(): void
    {
        // Arrange
        $data = [
            'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
        ];

        // Act
        $transaction = Transaction::fromData($data);

        // Assert
        $this->assertSame('9999', $transaction->total->getAmount());
        $this->assertSame('USD', $transaction->total->getCurrency()->getCode());
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
        $transaction = Transaction::fromData($data);

        // Assert
        $this->assertSame('9999', $transaction->total->getAmount());
        $this->assertSame('4111111111111111', $transaction->card->number);
        $this->assertSame('txn_123', $transaction->id);
        $this->assertSame(TransactionState::AUTHORIZED, $transaction->state);
        $this->assertSame('Order #12345', $transaction->description);
        $this->assertSame('REF-12345', $transaction->customReference);
        $this->assertSame('2025-11-13T10:00:00Z', $transaction->createdAt);
    }

    public function test_fromArray_withMissingTotal_createsTransactionWithNullTotal(): void
    {
        // Arrange
        $data = [
            'card' => ['number' => '4111111111111111'],
        ];

        // Act
        $transaction = Transaction::fromData($data);

        // Assert
        $this->assertNull($transaction->total);
        $this->assertNotNull($transaction->card);
        $this->assertSame('4111111111111111', $transaction->card->number);
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
        $this->expectExceptionMessage('Invalid state: invalid_state');

        // Act
        Transaction::fromData($data);
    }

    public function test_toArray_withMinimalData_returnsArray(): void
    {
        // Arrange
        $transaction = new Transaction(
            total: Money::USD(9999)
        );

        // Act
        $array = $transaction->toData();

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
            total: Money::USD(9999),
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
        $array = $transaction->toData();

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
            'state' => 'authorized',
            'id' => 'txn_123',
            'description' => 'Order #12345',
            'customReference' => 'REF-12345',
            'createdAt' => '2025-11-13T10:00:00Z',
        ], $array);
    }

    public function test_toArray_onlyIncludesNonNullValues(): void
    {
        // Arrange
        $transaction = new Transaction(
            total: Money::USD(9999),
            description: 'Test',
        );

        // Act
        $array = $transaction->toData();

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
        $transaction = Transaction::fromData($originalData);
        $resultData = $transaction->toData();

        // Assert
        $this->assertSame($originalData, $resultData);
    }

    public function test_properties_totalIsReadonly(): void
    {
        // Arrange
        $transaction = new Transaction(
            total: Money::USD(9999)
        );

        // Act & Assert
        $reflection = new \ReflectionProperty($transaction, 'total');
        $this->assertTrue($reflection->isReadOnly());
    }

    public function test_mixedConstruction_moneyObjectAndCardArray(): void
    {
        // Arrange
        $money = Money::USD(9999); // 99.99 in cents

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
            total: Money::USD(9999),
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
            total: Money::USD(9999),
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

    public function test_construct_withShopperValueObjectsAsStrings_createsInstances(): void
    {
        // Arrange & Act
        $transaction = new Transaction(
            total: Money::USD(5000),
            shopperIpAddress: '192.168.1.100',
            shopperLanguageTag: 'en-US',
            shopperTimeZone: 'America/New_York',
        );

        // Assert
        $this->assertInstanceOf(IpAddress::class, $transaction->shopperIpAddress);
        $this->assertSame('192.168.1.100', $transaction->shopperIpAddress->address);

        $this->assertInstanceOf(LanguageTag::class, $transaction->shopperLanguageTag);
        $this->assertSame('en-US', $transaction->shopperLanguageTag->tag);

        $this->assertInstanceOf(TimeZone::class, $transaction->shopperTimeZone);
        $this->assertSame('America/New_York', $transaction->shopperTimeZone->timezone);
    }

    public function test_construct_withShopperValueObjects_storesInstances(): void
    {
        // Arrange
        $ipAddress = new IpAddress('10.9.234.22');
        $languageTag = new LanguageTag('fr-FR');
        $timeZone = new TimeZone('Europe/Paris');

        // Act
        $transaction = new Transaction(
            total: Money::EUR(10000),
            shopperIpAddress: $ipAddress,
            shopperLanguageTag: $languageTag,
            shopperTimeZone: $timeZone,
        );

        // Assert
        $this->assertSame($ipAddress, $transaction->shopperIpAddress);
        $this->assertSame($languageTag, $transaction->shopperLanguageTag);
        $this->assertSame($timeZone, $transaction->shopperTimeZone);
    }

    public function test_toData_withShopperValueObjects_serializesToStrings(): void
    {
        // Arrange
        $transaction = new Transaction(
            total: Money::GBP(7500),
            shopperIpAddress: '2001:db8::1',
            shopperLanguageTag: 'en-GB',
            shopperTimeZone: 'Europe/London',
        );

        // Act
        $data = $transaction->toData();

        // Assert
        $this->assertIsArray($data);
        $this->assertSame('2001:db8::1', $data['shopperIpAddress']);
        $this->assertSame('en-GB', $data['shopperLanguageTag']);
        $this->assertSame('Europe/London', $data['shopperTimeZone']);
    }

    public function test_fromData_withShopperFields_createsValueObjects(): void
    {
        // Arrange
        $data = [
            'total' => ['amount' => '250.00', 'currencyCode' => 'JPY'],
            'shopperIpAddress' => '203.0.113.42',
            'shopperLanguageTag' => 'ja-JP',
            'shopperTimeZone' => 'Asia/Tokyo',
        ];

        // Act
        $transaction = Transaction::fromData($data);

        // Assert
        $this->assertInstanceOf(IpAddress::class, $transaction->shopperIpAddress);
        $this->assertSame('203.0.113.42', $transaction->shopperIpAddress->address);

        $this->assertInstanceOf(LanguageTag::class, $transaction->shopperLanguageTag);
        $this->assertSame('ja-JP', $transaction->shopperLanguageTag->tag);

        $this->assertInstanceOf(TimeZone::class, $transaction->shopperTimeZone);
        $this->assertSame('Asia/Tokyo', $transaction->shopperTimeZone->timezone);
    }

    public function test_roundTrip_withShopperValueObjects_preservesData(): void
    {
        // Arrange
        $originalData = [
            'total' => ['amount' => '150.00', 'currencyCode' => 'AUD'],
            'shopperIpAddress' => '::1',
            'shopperLanguageTag' => 'en-AU',
            'shopperTimeZone' => 'Australia/Sydney',
        ];

        // Act
        $transaction = Transaction::fromData($originalData);
        $restoredData = $transaction->toData();

        // Assert
        $this->assertSame($originalData['shopperIpAddress'], $restoredData['shopperIpAddress']);
        $this->assertSame($originalData['shopperLanguageTag'], $restoredData['shopperLanguageTag']);
        $this->assertSame($originalData['shopperTimeZone'], $restoredData['shopperTimeZone']);
    }
}