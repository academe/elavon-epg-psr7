<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\CountAndTotal;
use Academe\Elavon\Epg\Psr7\Enums\Currency;
use Academe\Elavon\Epg\Psr7\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

/**
 * Tests for CountAndTotal DTO.
 */
class CountAndTotalTest extends TestCase
{
    public function test_construct_withMinimalFields_createsInstance(): void
    {
        // Arrange & Act
        $countAndTotal = new CountAndTotal();

        // Assert
        $this->assertNull($countAndTotal->count);
        $this->assertNull($countAndTotal->total);
    }

    public function test_construct_withAllFields_createsInstance(): void
    {
        // Arrange & Act
        $countAndTotal = new CountAndTotal(
            count: 5,
            total: ['amount' => '100.00', 'currencyCode' => 'USD']
        );

        // Assert
        $this->assertSame(5, $countAndTotal->count);
        $this->assertInstanceOf(Money::class, $countAndTotal->total);
        $this->assertSame('100.00', $countAndTotal->total->amount);
        $this->assertSame(Currency::USD, $countAndTotal->total->currency);
    }

    public function test_construct_withMoneyObject_createsInstance(): void
    {
        // Arrange
        $money = new Money('250.00', Currency::EUR);

        // Act
        $countAndTotal = new CountAndTotal(
            count: 10,
            total: $money
        );

        // Assert
        $this->assertSame(10, $countAndTotal->count);
        $this->assertSame($money, $countAndTotal->total);
    }

    public function test_construct_withZeroCount_createsInstance(): void
    {
        // Arrange & Act
        $countAndTotal = new CountAndTotal(
            count: 0,
            total: ['amount' => '0.00', 'currencyCode' => 'USD']
        );

        // Assert
        $this->assertSame(0, $countAndTotal->count);
        $this->assertSame('0.00', $countAndTotal->total->amount);
    }

    public function test_fromData_withMinimalData_createsInstance(): void
    {
        // Arrange
        $data = [];

        // Act
        $countAndTotal = CountAndTotal::fromData($data);

        // Assert
        $this->assertNull($countAndTotal->count);
        $this->assertNull($countAndTotal->total);
    }

    public function test_fromData_withFullData_createsInstance(): void
    {
        // Arrange
        $data = [
            'count' => 3,
            'total' => [
                'amount' => '22.00',
                'currencyCode' => 'EUR',
            ],
        ];

        // Act
        $countAndTotal = CountAndTotal::fromData($data);

        // Assert
        $this->assertSame(3, $countAndTotal->count);
        $this->assertInstanceOf(Money::class, $countAndTotal->total);
        $this->assertSame('22.00', $countAndTotal->total->amount);
        $this->assertSame(Currency::EUR, $countAndTotal->total->currency);
    }

    public function test_fromData_withCountOnly_createsInstance(): void
    {
        // Arrange
        $data = ['count' => 7];

        // Act
        $countAndTotal = CountAndTotal::fromData($data);

        // Assert
        $this->assertSame(7, $countAndTotal->count);
        $this->assertNull($countAndTotal->total);
    }

    public function test_fromData_withTotalOnly_createsInstance(): void
    {
        // Arrange
        $data = [
            'total' => [
                'amount' => '99.99',
                'currencyCode' => 'GBP',
            ],
        ];

        // Act
        $countAndTotal = CountAndTotal::fromData($data);

        // Assert
        $this->assertNull($countAndTotal->count);
        $this->assertInstanceOf(Money::class, $countAndTotal->total);
        $this->assertSame('99.99', $countAndTotal->total->amount);
        $this->assertSame(Currency::GBP, $countAndTotal->total->currency);
    }

    public function test_toData_withMinimalData_returnsArray(): void
    {
        // Arrange
        $countAndTotal = new CountAndTotal();

        // Act
        $array = $countAndTotal->toData();

        // Assert
        $this->assertSame([], $array);
    }

    public function test_toData_withFullData_returnsArray(): void
    {
        // Arrange
        $countAndTotal = new CountAndTotal(
            count: 15,
            total: ['amount' => '500.00', 'currencyCode' => 'USD']
        );

        // Act
        $array = $countAndTotal->toData();

        // Assert - Order follows SerializesData trait: object, array, enum, scalar
        $this->assertSame([
            'total' => [
                'amount' => '500.00',
                'currencyCode' => 'USD',
            ],
            'count' => 15,
        ], $array);
    }

    public function test_toData_onlyIncludesNonNullValues(): void
    {
        // Arrange
        $countAndTotal = new CountAndTotal(
            count: 5
        );

        // Act
        $array = $countAndTotal->toData();

        // Assert
        $this->assertArrayHasKey('count', $array);
        $this->assertArrayNotHasKey('total', $array);
    }

    public function test_roundTrip_fromDataToData_preservesData(): void
    {
        // Arrange
        $originalData = [
            'count' => 8,
            'total' => [
                'amount' => '150.00',
                'currencyCode' => 'USD',
            ],
        ];

        // Act
        $countAndTotal = CountAndTotal::fromData($originalData);
        $resultData = $countAndTotal->toData();

        // Assert - Check individual fields as order may differ
        $this->assertSame(8, $resultData['count']);
        $this->assertSame($originalData['total'], $resultData['total']);
    }

    public function test_properties_areReadonly(): void
    {
        // Arrange
        $countAndTotal = new CountAndTotal(count: 1);

        // Act & Assert
        $reflection = new \ReflectionProperty($countAndTotal, 'count');
        $this->assertTrue($reflection->isReadOnly());

        $reflection = new \ReflectionProperty($countAndTotal, 'total');
        $this->assertTrue($reflection->isReadOnly());
    }

    public function test_construct_withNegativeCount_createsInstance(): void
    {
        // Arrange & Act (negative count allowed for refunds/reversals)
        $countAndTotal = new CountAndTotal(
            count: -5,
            total: ['amount' => '-100.00', 'currencyCode' => 'USD']
        );

        // Assert
        $this->assertSame(-5, $countAndTotal->count);
        $this->assertSame('-100.00', $countAndTotal->total->amount);
    }
}
