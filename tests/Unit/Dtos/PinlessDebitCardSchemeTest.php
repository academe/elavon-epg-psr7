<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\PinlessDebitCardScheme;
use Academe\Elavon\Epg\Psr7\Enums\CardScheme;
use Money\Money;
use PHPUnit\Framework\TestCase;

/**
 * Tests for PinlessDebitCardScheme DTO.
 */
class PinlessDebitCardSchemeTest extends TestCase
{
    public function test_construct_withNoFields_createsInstance(): void
    {
        // Arrange & Act
        $pinlessDebit = new PinlessDebitCardScheme();

        // Assert
        $this->assertNull($pinlessDebit->cardScheme);
        $this->assertNull($pinlessDebit->isEnabled);
        $this->assertNull($pinlessDebit->threshold);
    }

    public function test_construct_withAllFields_createsInstance(): void
    {
        // Arrange & Act
        $pinlessDebit = new PinlessDebitCardScheme(
            cardScheme: CardScheme::VISA,
            isEnabled: true,
            threshold: Money::USD(5000),
        );

        // Assert
        $this->assertSame(CardScheme::VISA, $pinlessDebit->cardScheme);
        $this->assertTrue($pinlessDebit->isEnabled);
        $this->assertInstanceOf(Money::class, $pinlessDebit->threshold);
        $this->assertSame('5000', $pinlessDebit->threshold->getAmount());
        $this->assertSame('USD', $pinlessDebit->threshold->getCurrency()->getCode());
    }

    public function test_construct_withPartialFields_createsInstance(): void
    {
        // Arrange & Act
        $pinlessDebit = new PinlessDebitCardScheme(
            cardScheme: CardScheme::MASTERCARD,
            isEnabled: false,
        );

        // Assert
        $this->assertSame(CardScheme::MASTERCARD, $pinlessDebit->cardScheme);
        $this->assertFalse($pinlessDebit->isEnabled);
        $this->assertNull($pinlessDebit->threshold);
    }

    public function test_construct_withDisabled_createsInstance(): void
    {
        // Arrange & Act
        $pinlessDebit = new PinlessDebitCardScheme(
            cardScheme: CardScheme::DISCOVER,
            isEnabled: false,
        );

        // Assert
        $this->assertSame(CardScheme::DISCOVER, $pinlessDebit->cardScheme);
        $this->assertFalse($pinlessDebit->isEnabled);
    }

    public function test_fromData_withEmptyData_createsInstance(): void
    {
        // Arrange
        $data = [];

        // Act
        $pinlessDebit = PinlessDebitCardScheme::fromData($data);

        // Assert
        $this->assertNull($pinlessDebit->cardScheme);
        $this->assertNull($pinlessDebit->isEnabled);
        $this->assertNull($pinlessDebit->threshold);
    }

    public function test_fromData_withAllFields_createsInstance(): void
    {
        // Arrange
        $data = [
            'cardScheme' => 'Visa',
            'isEnabled' => true,
            'threshold' => [
                'amountMinor' => '5000', // Minor units
                'currencyCode' => 'USD',
            ],
        ];

        // Act
        $pinlessDebit = PinlessDebitCardScheme::fromData($data);

        // Assert
        $this->assertSame(CardScheme::VISA, $pinlessDebit->cardScheme);
        $this->assertTrue($pinlessDebit->isEnabled);
        $this->assertInstanceOf(Money::class, $pinlessDebit->threshold);
        $this->assertSame('5000', $pinlessDebit->threshold->getAmount());
        $this->assertSame('USD', $pinlessDebit->threshold->getCurrency()->getCode());
    }

    public function test_fromData_withPartialFields_createsInstance(): void
    {
        // Arrange
        $data = [
            'cardScheme' => 'MasterCard',
            'isEnabled' => false,
        ];

        // Act
        $pinlessDebit = PinlessDebitCardScheme::fromData($data);

        // Assert
        $this->assertSame(CardScheme::MASTERCARD, $pinlessDebit->cardScheme);
        $this->assertFalse($pinlessDebit->isEnabled);
        $this->assertNull($pinlessDebit->threshold);
    }

    public function test_fromData_withDifferentCardSchemes_createsInstance(): void
    {
        // Test various card schemes
        $cardSchemes = [
            'American Express' => CardScheme::AMERICAN_EXPRESS,
            'Diners Club' => CardScheme::DINERS_CLUB,
            'Discover' => CardScheme::DISCOVER,
            'JCB' => CardScheme::JCB,
            'Maestro' => CardScheme::MAESTRO,
            'MasterCard' => CardScheme::MASTERCARD,
            'UnionPay' => CardScheme::UNION_PAY,
            'Visa' => CardScheme::VISA,
        ];

        foreach ($cardSchemes as $stringValue => $expectedEnum) {
            $pinlessDebit = PinlessDebitCardScheme::fromData([
                'cardScheme' => $stringValue,
                'isEnabled' => true,
            ]);

            $this->assertSame($expectedEnum, $pinlessDebit->cardScheme);
        }
    }

    public function test_toData_withNoFields_returnsEmptyArray(): void
    {
        // Arrange
        $pinlessDebit = new PinlessDebitCardScheme();

        // Act
        $array = $pinlessDebit->toData();

        // Assert
        $this->assertSame([], $array);
    }

    public function test_toData_withAllFields_returnsArray(): void
    {
        // Arrange
        $pinlessDebit = new PinlessDebitCardScheme(
            cardScheme: CardScheme::VISA,
            isEnabled: true,
            threshold: Money::USD(5000),
        );

        // Act
        $array = $pinlessDebit->toData();

        // Assert
        $this->assertSame('Visa', $array['cardScheme']);
        $this->assertTrue($array['isEnabled']);
        $this->assertSame([
            'amount' => '50.00',
            'currencyCode' => 'USD',
        ], $array['threshold']);
    }

    public function test_toData_withPartialFields_returnsOnlyNonNullValues(): void
    {
        // Arrange
        $pinlessDebit = new PinlessDebitCardScheme(
            cardScheme: CardScheme::MASTERCARD,
            isEnabled: false,
        );

        // Act
        $array = $pinlessDebit->toData();

        // Assert
        $this->assertArrayHasKey('cardScheme', $array);
        $this->assertArrayHasKey('isEnabled', $array);
        $this->assertArrayNotHasKey('threshold', $array);
        $this->assertSame('MasterCard', $array['cardScheme']);
        $this->assertFalse($array['isEnabled']);
    }

    public function test_roundTrip_fromDataToData_preservesData(): void
    {
        // Arrange
        $originalData = [
            'cardScheme' => 'Visa',
            'isEnabled' => true,
            'threshold' => [
                'amount' => '100.00', // Major units
                'currencyCode' => 'EUR',
            ],
        ];

        // Act
        $pinlessDebit = PinlessDebitCardScheme::fromData($originalData);
        $resultData = $pinlessDebit->toData();

        // Assert
        $this->assertSame($originalData['cardScheme'], $resultData['cardScheme']);
        $this->assertSame($originalData['isEnabled'], $resultData['isEnabled']);
        $this->assertSame($originalData['threshold'], $resultData['threshold']);
    }

    public function test_properties_areReadonly(): void
    {
        // Arrange
        $pinlessDebit = new PinlessDebitCardScheme(cardScheme: CardScheme::VISA);

        // Act & Assert
        $reflection = new \ReflectionProperty($pinlessDebit, 'cardScheme');
        $this->assertTrue($reflection->isReadOnly());

        $reflection = new \ReflectionProperty($pinlessDebit, 'isEnabled');
        $this->assertTrue($reflection->isReadOnly());

        $reflection = new \ReflectionProperty($pinlessDebit, 'threshold');
        $this->assertTrue($reflection->isReadOnly());
    }

    public function test_toObjectArray_returnsShallowArray(): void
    {
        // Arrange
        $pinlessDebit = new PinlessDebitCardScheme(
            cardScheme: CardScheme::VISA,
            isEnabled: true,
            threshold: Money::USD(5000),
        );

        // Act
        $array = $pinlessDebit->toObjectArray();

        // Assert
        $this->assertCount(3, $array);
        $this->assertSame(CardScheme::VISA, $array['cardScheme']);
        $this->assertTrue($array['isEnabled']);
        $this->assertInstanceOf(Money::class, $array['threshold']);
    }
}
