<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\Contact;
use Academe\Elavon\Epg\Psr7\Dtos\Order;
use Academe\Elavon\Epg\Psr7\Dtos\OrderItem;
use Academe\Elavon\Epg\Psr7\Enums\OrderItemType;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Money\Money;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Order DTO.
 */
class OrderTest extends TestCase
{
    public function test_construct_withMinimalFields_createsInstance(): void
    {
        // Arrange & Act
        $order = new Order(
            total: Money::USD(10000)
        );

        // Assert
        $this->assertInstanceOf(Money::class, $order->total);
        $this->assertSame('10000', $order->total->getAmount());
        $this->assertSame('USD', $order->total->getCurrency()->getCode());
        $this->assertNull($order->id);
        $this->assertNull($order->description);
        $this->assertNull($order->items);
        $this->assertNull($order->shipTo);
    }

    public function test_construct_withAllFields_createsInstance(): void
    {
        // Arrange & Act
        $order = new Order(
            href: 'https://api.example.com/orders/ord123',
            id: 'ord123',
            createdAt: '2025-11-19T10:00:00Z',
            modifiedAt: '2025-11-19T11:00:00Z',
            merchant: 'https://api.example.com/merchants/m123',
            total: Money::USD(25000),
            description: 'March 2025 Rent',
            items: [
                ['total' => ['amount' => '250.00', 'currencyCode' => 'USD'], 'description' => 'Rent'],
            ],
            shipTo: ['fullName' => 'John Doe', 'street1' => '123 Main St'],
            shopperEmailAddress: 'shopper@example.com',
            shopperReference: 'PO-12345',
            orderReference: 'ORD-67890',
            customReference: 'CUST-REF-111',
            customFields: ['field1' => 'value1'],
        );

        // Assert
        $this->assertSame('https://api.example.com/orders/ord123', $order->href);
        $this->assertSame('ord123', $order->id);
        $this->assertSame('2025-11-19T10:00:00Z', $order->createdAt);
        $this->assertSame('2025-11-19T11:00:00Z', $order->modifiedAt);
        $this->assertSame('https://api.example.com/merchants/m123', $order->merchant);
        $this->assertSame('25000', $order->total->getAmount());
        $this->assertSame('March 2025 Rent', $order->description);
        $this->assertIsArray($order->items);
        $this->assertCount(1, $order->items);
        $this->assertInstanceOf(OrderItem::class, $order->items[0]);
        $this->assertInstanceOf(Contact::class, $order->shipTo);
        $this->assertSame('shopper@example.com', $order->shopperEmailAddress);
        $this->assertSame('PO-12345', $order->shopperReference);
        $this->assertSame('ORD-67890', $order->orderReference);
        $this->assertSame('CUST-REF-111', $order->customReference);
        $this->assertSame(['field1' => 'value1'], $order->customFields);
    }

    public function test_construct_withMoneyObject_createsInstance(): void
    {
        // Arrange
        $money = Money::EUR(15000); // 150.00 EUR

        // Act
        $order = new Order(total: $money);

        // Assert
        $this->assertSame($money, $order->total);
    }

    public function test_construct_withContactObject_createsInstance(): void
    {
        // Arrange
        $contact = new Contact(
            fullName: 'Jane Smith',
            street1: '456 Oak Ave',
        );

        // Act
        $order = new Order(
            total: Money::USD(7500),
            shipTo: $contact,
        );

        // Assert
        $this->assertSame($contact, $order->shipTo);
    }

    public function test_construct_withOrderItemObjects_createsInstance(): void
    {
        // Arrange
        $item = new OrderItem(
            total: Money::USD(5000),
            description: 'Service item',
        );

        // Act
        $order = new Order(
            total: Money::USD(5000),
            items: [$item],
        );

        // Assert
        $this->assertCount(1, $order->items);
        $this->assertSame($item, $order->items[0]);
    }

    public function test_construct_withTooLongDescription_throwsException(): void
    {
        // Arrange
        $longDescription = str_repeat('a', 256);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Description must not exceed 255 characters');

        // Act
        new Order(
            total: Money::USD(1000),
            description: $longDescription,
        );
    }

    public function test_construct_withTooLongShopperEmail_throwsException(): void
    {
        // Arrange
        $longEmail = str_repeat('a', 255) . '@example.com';

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Shopper email address must not exceed 254 characters');

        // Act
        new Order(
            total: Money::USD(1000),
            shopperEmailAddress: $longEmail,
        );
    }

    public function test_construct_withTooLongShopperReference_throwsException(): void
    {
        // Arrange
        $longRef = str_repeat('a', 256);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Shopper reference must not exceed 255 characters');

        // Act
        new Order(
            total: Money::USD(1000),
            shopperReference: $longRef,
        );
    }

    public function test_construct_withTooLongOrderReference_throwsException(): void
    {
        // Arrange
        $longRef = str_repeat('a', 256);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Order reference must not exceed 255 characters');

        // Act
        new Order(
            total: Money::USD(1000),
            orderReference: $longRef,
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
        new Order(
            total: Money::USD(1000),
            customReference: $longRef,
        );
    }

    public function test_construct_withTooManyItems_throwsException(): void
    {
        // Arrange
        $items = [];
        for ($i = 0; $i < 65; $i++) {
            $items[] = ['total' => ['amount' => '1.00', 'currencyCode' => 'USD']];
        }

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Items must not exceed 64 entries');

        // Act
        new Order(
            total: Money::USD(1000),
            items: $items,
        );
    }

    public function test_construct_withInvalidCustomFieldName_throwsException(): void
    {
        // Arrange
        $longKey = str_repeat('a', 65);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Custom field names must not exceed 64 characters');

        // Act
        new Order(
            total: Money::USD(1000),
            customFields: [$longKey => 'value'],
        );
    }

    public function test_construct_withInvalidCustomFieldValue_throwsException(): void
    {
        // Arrange
        $longValue = str_repeat('a', 1025);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Custom field values must not exceed 1024 characters');

        // Act
        new Order(
            total: Money::USD(1000),
            customFields: ['field1' => $longValue],
        );
    }

    public function test_fromData_withMinimalData_createsInstance(): void
    {
        // Arrange
        $data = [
            'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
        ];

        // Act
        $order = Order::fromData($data);

        // Assert
        $this->assertSame('9999', $order->total->getAmount());
        $this->assertNull($order->id);
        $this->assertNull($order->description);
    }

    public function test_fromData_withFullData_createsInstance(): void
    {
        // Arrange
        $data = [
            'href' => 'https://api.example.com/orders/o123',
            'id' => 'o123',
            'createdAt' => '2025-11-19T10:00:00Z',
            'modifiedAt' => '2025-11-19T11:00:00Z',
            'merchant' => 'https://api.example.com/merchants/m123',
            'total' => ['amount' => '500.00', 'currencyCode' => 'GBP'],
            'description' => 'Q1 Services',
            'items' => [
                [
                    'total' => ['amount' => '250.00', 'currencyCode' => 'GBP'],
                    'description' => 'Consulting',
                    'type' => 'service',
                ],
                [
                    'total' => ['amount' => '250.00', 'currencyCode' => 'GBP'],
                    'description' => 'Materials',
                    'type' => 'goods',
                ],
            ],
            'shipTo' => [
                'fullName' => 'Alice Johnson',
                'street1' => '789 Elm St',
                'city' => 'London',
            ],
            'shopperEmailAddress' => 'alice@example.com',
            'shopperReference' => 'PO-999',
            'orderReference' => 'ORD-888',
            'customReference' => 'CUSTOM-777',
            'customFields' => ['project' => 'Alpha'],
        ];

        // Act
        $order = Order::fromData($data);

        // Assert
        $this->assertSame('o123', $order->id);
        $this->assertSame('50000', $order->total->getAmount());
        $this->assertSame('Q1 Services', $order->description);
        $this->assertCount(2, $order->items);
        $this->assertInstanceOf(OrderItem::class, $order->items[0]);
        $this->assertSame(OrderItemType::SERVICE, $order->items[0]->type);
        $this->assertInstanceOf(OrderItem::class, $order->items[1]);
        $this->assertSame(OrderItemType::GOODS, $order->items[1]->type);
        $this->assertInstanceOf(Contact::class, $order->shipTo);
        $this->assertSame('Alice Johnson', $order->shipTo->fullName);
        $this->assertSame('alice@example.com', $order->shopperEmailAddress);
    }

    public function test_toData_withMinimalData_returnsArray(): void
    {
        // Arrange
        $order = new Order(
            total: Money::USD(5000)
        );

        // Act
        $array = $order->toData();

        // Assert
        $this->assertSame([
            'total' => [
                'amount' => '50.00',
                'currencyCode' => 'USD',
            ],
        ], $array);
    }

    public function test_toData_withFullData_returnsArray(): void
    {
        // Arrange
        $order = new Order(
            total: Money::EUR(30000),
            description: 'Annual subscription',
            items: [
                [
                    'total' => ['amount' => '300.00', 'currencyCode' => 'EUR'],
                    'description' => 'Premium plan',
                    'type' => 'service',
                ],
            ],
            shopperEmailAddress: 'customer@example.com',
            customReference: 'SUB-2025',
        );

        // Act
        $array = $order->toData();

        // Assert
        $this->assertArrayHasKey('total', $array);
        $this->assertSame('300.00', $array['total']['amount']);
        $this->assertSame('Annual subscription', $array['description']);
        $this->assertCount(1, $array['items']);
        $this->assertSame('Premium plan', $array['items'][0]['description']);
        $this->assertSame('customer@example.com', $array['shopperEmailAddress']);
        $this->assertSame('SUB-2025', $array['customReference']);
    }

    public function test_toData_onlyIncludesNonNullValues(): void
    {
        // Arrange
        $order = new Order(
            total: Money::USD(2500),
            description: 'Test order',
        );

        // Act
        $array = $order->toData();

        // Assert
        $this->assertArrayHasKey('total', $array);
        $this->assertArrayHasKey('description', $array);
        $this->assertArrayNotHasKey('id', $array);
        $this->assertArrayNotHasKey('items', $array);
        $this->assertArrayNotHasKey('shipTo', $array);
        $this->assertArrayNotHasKey('shopperEmailAddress', $array);
    }

    public function test_roundTrip_fromDataToData_preservesData(): void
    {
        // Arrange
        $originalData = [
            'total' => ['amount' => '150.00', 'currencyCode' => 'USD'],
            'description' => 'Test order',
            'items' => [
                [
                    'total' => ['amount' => '150.00', 'currencyCode' => 'USD'],
                    'description' => 'Item 1',
                ],
            ],
            'shopperEmailAddress' => 'test@example.com',
        ];

        // Act
        $order = Order::fromData($originalData);
        $resultData = $order->toData();

        // Assert - Check field by field as order may differ
        $this->assertSame($originalData['total'], $resultData['total']);
        $this->assertSame($originalData['description'], $resultData['description']);
        $this->assertCount(1, $resultData['items']);
        $this->assertSame($originalData['items'][0]['total'], $resultData['items'][0]['total']);
        $this->assertSame($originalData['items'][0]['description'], $resultData['items'][0]['description']);
        $this->assertSame($originalData['shopperEmailAddress'], $resultData['shopperEmailAddress']);
    }

    public function test_properties_areReadonly(): void
    {
        // Arrange
        $order = new Order(
            total: Money::USD(1000)
        );

        // Act & Assert
        $reflection = new \ReflectionProperty($order, 'total');
        $this->assertTrue($reflection->isReadOnly());
    }
}
