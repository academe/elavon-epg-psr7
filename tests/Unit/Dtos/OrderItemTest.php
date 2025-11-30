<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\OrderItem;
use Academe\Elavon\Epg\Psr7\Enums\OrderItemType;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Money\Money;
use PHPUnit\Framework\TestCase;

/**
 * Tests for OrderItem DTO.
 */
class OrderItemTest extends TestCase
{
    public function test_construct_withMinimalFields_createsInstance(): void
    {
        // Arrange & Act
        $item = new OrderItem(
            total: Money::USD(5000)
        );

        // Assert
        $this->assertInstanceOf(Money::class, $item->total);
        $this->assertSame('5000', $item->total->getAmount());
        $this->assertSame('USD', $item->total->getCurrency()->getCode());
        $this->assertNull($item->description);
        $this->assertNull($item->unitPrice);
        $this->assertNull($item->quantity);
        $this->assertNull($item->customReference);
        $this->assertNull($item->type);
    }

    public function test_construct_withAllFields_createsInstance(): void
    {
        // Arrange & Act
        $item = new OrderItem(
            total: Money::USD(10000),
            description: 'Oil change service',
            unitPrice: Money::USD(2500),
            quantity: 4,
            customReference: 'SERVICE-123',
            type: OrderItemType::SERVICE,
        );

        // Assert
        $this->assertSame('10000', $item->total->getAmount());
        $this->assertSame('Oil change service', $item->description);
        $this->assertInstanceOf(Money::class, $item->unitPrice);
        $this->assertSame('2500', $item->unitPrice->getAmount());
        $this->assertSame(4, $item->quantity);
        $this->assertSame('SERVICE-123', $item->customReference);
        $this->assertSame(OrderItemType::SERVICE, $item->type);
    }

    public function test_construct_withMoneyObjects_createsInstance(): void
    {
        // Arrange
        $total = Money::EUR(7500); // 75.00 EUR
        $unitPrice = Money::EUR(2500); // 25.00 EUR

        // Act
        $item = new OrderItem(
            total: $total,
            unitPrice: $unitPrice,
        );

        // Assert
        $this->assertSame($total, $item->total);
        $this->assertSame($unitPrice, $item->unitPrice);
    }

    public function test_construct_withTypeString_normalizesToEnum(): void
    {
        // Arrange & Act
        $item = OrderItem::fromData([
            'total' => Money::USD(1000),
            'type' => 'goods',
        ]);

        // Assert
        $this->assertSame(OrderItemType::GOODS, $item->type);
    }

    public function test_construct_withInvalidType_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid enum value for');

        // Act
        OrderItem::fromData([
            'total' => Money::USD(1000),
            'type' => 'invalid_type',
        ]);
    }

    public function test_construct_withEmptyDescription_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Description must be between 1 and 255 characters');

        // Act
        new OrderItem(
            total: Money::USD(1000),
            description: '',
        );
    }

    public function test_construct_withTooLongDescription_throwsException(): void
    {
        // Arrange
        $longDescription = str_repeat('a', 256);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Description must be between 1 and 255 characters');

        // Act
        new OrderItem(
            total: Money::USD(1000),
            description: $longDescription,
        );
    }

    public function test_construct_withTooLongCustomReference_throwsException(): void
    {
        // Arrange
        $longRef = str_repeat('a', 256);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Custom reference must not exceed 255 characters');

        // Act
        new OrderItem(
            total: Money::USD(1000),
            customReference: $longRef,
        );
    }

    public function test_construct_withZeroQuantity_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be at least 1');

        // Act
        new OrderItem(
            total: Money::USD(1000),
            quantity: 0,
        );
    }

    public function test_construct_withNegativeQuantity_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be at least 1');

        // Act
        new OrderItem(
            total: Money::USD(1000),
            quantity: -1,
        );
    }

    public function test_fromData_withMinimalData_createsInstance(): void
    {
        // Arrange
        $data = [
            'total' => ['amount' => '99.99', 'currencyCode' => 'GBP'],
        ];

        // Act
        $item = OrderItem::fromData($data);

        // Assert
        $this->assertSame('9999', $item->total->getAmount());
        $this->assertSame('GBP', $item->total->getCurrency()->getCode());
        $this->assertNull($item->description);
    }

    public function test_fromData_withFullData_createsInstance(): void
    {
        // Arrange
        $data = [
            'total' => ['amount' => '200.00', 'currencyCode' => 'USD'],
            'description' => 'Premium service',
            'unitPrice' => ['amount' => '50.00', 'currencyCode' => 'USD'],
            'quantity' => 4,
            'customReference' => 'REF-456',
            'type' => 'service',
        ];

        // Act
        $item = OrderItem::fromData($data);

        // Assert
        $this->assertSame('20000', $item->total->getAmount());
        $this->assertSame('Premium service', $item->description);
        $this->assertSame('5000', $item->unitPrice->getAmount());
        $this->assertSame(4, $item->quantity);
        $this->assertSame('REF-456', $item->customReference);
        $this->assertSame(OrderItemType::SERVICE, $item->type);
    }

    public function test_toData_withMinimalData_returnsArray(): void
    {
        // Arrange
        $item = new OrderItem(
            total: Money::USD(3000)
        );

        // Act
        $array = $item->toData();

        // Assert
        $this->assertSame([
            'total' => [
                'amount' => '30.00',
                'currencyCode' => 'USD',
            ],
        ], $array);
    }

    public function test_toData_withFullData_returnsArray(): void
    {
        // Arrange
        $item = new OrderItem(
            total: Money::EUR(12000),
            description: 'Consulting services',
            unitPrice: Money::EUR(6000),
            quantity: 2,
            customReference: 'CONSULT-789',
            type: OrderItemType::SERVICE,
        );

        // Act
        $array = $item->toData();

        // Assert
        $this->assertEquals([
            'total' => [
                'amount' => '120.00',
                'currencyCode' => 'EUR',
            ],
            'unitPrice' => [
                'amount' => '60.00',
                'currencyCode' => 'EUR',
            ],
            'type' => 'service',
            'description' => 'Consulting services',
            'customReference' => 'CONSULT-789',
            'quantity' => 2,
        ], $array);
    }

    public function test_toData_onlyIncludesNonNullValues(): void
    {
        // Arrange
        $item = new OrderItem(
            total: Money::USD(1500),
            description: 'Test item',
        );

        // Act
        $array = $item->toData();

        // Assert
        $this->assertArrayHasKey('total', $array);
        $this->assertArrayHasKey('description', $array);
        $this->assertArrayNotHasKey('unitPrice', $array);
        $this->assertArrayNotHasKey('quantity', $array);
        $this->assertArrayNotHasKey('customReference', $array);
        $this->assertArrayNotHasKey('type', $array);
    }

    public function test_roundTrip_fromDataToData_preservesData(): void
    {
        // Arrange
        $originalData = [
            'total' => ['amount' => '80.00', 'currencyCode' => 'USD'],
            'description' => 'Product sale',
            'unitPrice' => ['amount' => '20.00', 'currencyCode' => 'USD'],
            'quantity' => 4,
            'customReference' => 'PROD-111',
            'type' => 'goods',
        ];

        // Act
        $item = OrderItem::fromData($originalData);
        $resultData = $item->toData();

        // Assert - Check field by field as order may differ
        $this->assertSame($originalData['total'], $resultData['total']);
        $this->assertSame($originalData['description'], $resultData['description']);
        $this->assertSame($originalData['unitPrice'], $resultData['unitPrice']);
        $this->assertSame($originalData['quantity'], $resultData['quantity']);
        $this->assertSame($originalData['customReference'], $resultData['customReference']);
        $this->assertSame($originalData['type'], $resultData['type']);
        $this->assertCount(6, $resultData);
    }

    public function test_properties_areReadonly(): void
    {
        // Arrange
        $item = new OrderItem(
            total: Money::USD(1000)
        );

        // Act & Assert
        $reflection = new \ReflectionProperty($item, 'total');
        $this->assertTrue($reflection->isReadOnly());
    }
}
