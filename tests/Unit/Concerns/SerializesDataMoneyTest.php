<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Concerns;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;

/**
 * Tests for SerializesData trait's money type support.
 */
class SerializesDataMoneyTest extends TestCase
{
    public function test_fromData_withMoneyData_createsMoneyphpMoneyObject(): void
    {
        // Arrange
        $data = [
            'total' => [
                'amount' => '99.99',
                'currencyCode' => 'USD',
            ],
        ];

        // Act
        $dto = TestDtoWithMoney::fromData($data);

        // Assert
        $this->assertInstanceOf(Money::class, $dto->total);
        $this->assertSame('9999', $dto->total->getAmount());
        $this->assertSame('USD', $dto->total->getCurrency()->getCode());
    }

    public function test_fromData_withNullMoney_returnsNull(): void
    {
        // Arrange
        $data = [];

        // Act
        $dto = TestDtoWithMoney::fromData($data);

        // Assert
        $this->assertNull($dto->total);
    }

    public function test_fromData_withMissingAmount_returnsNull(): void
    {
        // Arrange
        $data = [
            'total' => [
                'currencyCode' => 'USD',
            ],
        ];

        // Act
        $dto = TestDtoWithMoney::fromData($data);

        // Assert
        $this->assertNull($dto->total);
    }

    public function test_fromData_withMissingCurrencyCode_returnsNull(): void
    {
        // Arrange
        $data = [
            'total' => [
                'amount' => '99.99',
            ],
        ];

        // Act
        $dto = TestDtoWithMoney::fromData($data);

        // Assert
        $this->assertNull($dto->total);
    }

    public function test_fromData_withNonArrayMoney_returnsNull(): void
    {
        // Arrange
        $data = [
            'total' => 'not-an-array',
        ];

        // Act
        $dto = TestDtoWithMoney::fromData($data);

        // Assert
        $this->assertNull($dto->total);
    }

    public function test_toData_withMoneyObject_returnsAmountAndCurrencyCode(): void
    {
        // Arrange
        $dto = new TestDtoWithMoney(
            total: Money::USD(9999)
        );

        // Act
        $data = $dto->toData();

        // Assert
        $this->assertArrayHasKey('total', $data);
        $this->assertSame('99.99', $data['total']['amount']);
        $this->assertSame('USD', $data['total']['currencyCode']);
    }

    public function test_toData_withNullMoney_excludesFromOutput(): void
    {
        // Arrange
        $dto = new TestDtoWithMoney(total: null);

        // Act
        $data = $dto->toData();

        // Assert
        $this->assertArrayNotHasKey('total', $data);
    }

    public function test_toObjectArray_withMoneyObject_includesMoneyObject(): void
    {
        // Arrange
        $money = Money::EUR(5000);
        $dto = new TestDtoWithMoney(total: $money);

        // Act
        $data = $dto->toObjectArray();

        // Assert
        $this->assertArrayHasKey('total', $data);
        $this->assertSame($money, $data['total']);
    }

    public function test_roundTrip_fromDataToData_preservesMoneyValue(): void
    {
        // Arrange
        $originalData = [
            'total' => [
                'amount' => '123.45',
                'currencyCode' => 'GBP',
            ],
        ];

        // Act
        $dto = TestDtoWithMoney::fromData($originalData);
        $resultData = $dto->toData();

        // Assert
        $this->assertSame($originalData['total']['amount'], $resultData['total']['amount']);
        $this->assertSame($originalData['total']['currencyCode'], $resultData['total']['currencyCode']);
    }

    public function test_fromData_withZeroAmount_createsMoneyphpMoneyObject(): void
    {
        // Arrange
        $data = [
            'total' => [
                'amount' => '0.00',
                'currencyCode' => 'USD',
            ],
        ];

        // Act
        $dto = TestDtoWithMoney::fromData($data);

        // Assert
        $this->assertInstanceOf(Money::class, $dto->total);
        $this->assertSame('0', $dto->total->getAmount());
    }

    public function test_fromData_withFourDecimalPlaces_createsMoneyphpMoneyObject(): void
    {
        // Arrange - some currencies use 4 decimal places
        $data = [
            'total' => [
                'amount' => '10.1234',
                'currencyCode' => 'CLF', // Chilean Unidad de Fomento uses 4 decimals
            ],
        ];

        // Act
        $dto = TestDtoWithMoney::fromData($data);

        // Assert
        $this->assertInstanceOf(Money::class, $dto->total);
        $this->assertSame('101234', $dto->total->getAmount());
        $this->assertSame('CLF', $dto->total->getCurrency()->getCode());
    }

    public function test_fromData_withMultipleMoneyProperties_parsesAll(): void
    {
        // Arrange
        $data = [
            'total' => [
                'amount' => '100.00',
                'currencyCode' => 'USD',
            ],
            'tip' => [
                'amount' => '15.00',
                'currencyCode' => 'USD',
            ],
        ];

        // Act
        $dto = TestDtoWithMultipleMoney::fromData($data);

        // Assert
        $this->assertInstanceOf(Money::class, $dto->total);
        $this->assertSame('10000', $dto->total->getAmount());
        $this->assertInstanceOf(Money::class, $dto->tip);
        $this->assertSame('1500', $dto->tip->getAmount());
    }

    public function test_toData_withMultipleMoneyProperties_serializesAll(): void
    {
        // Arrange
        $dto = new TestDtoWithMultipleMoney(
            total: Money::USD(10000),
            tip: Money::USD(1500)
        );

        // Act
        $data = $dto->toData();

        // Assert
        $this->assertSame('100.00', $data['total']['amount']);
        $this->assertSame('15.00', $data['tip']['amount']);
    }
}

/**
 * Test DTO with a single money property.
 */
class TestDtoWithMoney implements DataTransferObject
{
    use SerializesData;

    public static function getPropertyTypes(): array
    {
        return [
            'money' => ['total'],
        ];
    }

    public function __construct(
        public readonly ?Money $total = null,
    ) {
    }
}

/**
 * Test DTO with multiple money properties.
 */
class TestDtoWithMultipleMoney implements DataTransferObject
{
    use SerializesData;

    public static function getPropertyTypes(): array
    {
        return [
            'money' => ['total', 'tip'],
        ];
    }

    public function __construct(
        public readonly ?Money $total = null,
        public readonly ?Money $tip = null,
    ) {
    }
}
