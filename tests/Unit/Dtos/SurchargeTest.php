<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\Surcharge;
use Money\Money;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Surcharge data object.
 */
class SurchargeTest extends TestCase
{
    public function test_construct_withMoneyObjects_createsInstance(): void
    {
        // Arrange
        $unadjustedTotal = Money::USD(10000); // 100.00 USD
        $unadjustedRefundableTotal = Money::USD(10000);
        $surchargeTotal = Money::USD(350); // 3.50 USD

        // Act
        $surcharge = new Surcharge(
            unadjustedTotal: $unadjustedTotal,
            unadjustedRefundableTotal: $unadjustedRefundableTotal,
            surchargeTotal: $surchargeTotal,
            rate: '0.035'
        );

        // Assert
        $this->assertSame($unadjustedTotal, $surcharge->unadjustedTotal);
        $this->assertSame($unadjustedRefundableTotal, $surcharge->unadjustedRefundableTotal);
        $this->assertSame($surchargeTotal, $surcharge->surchargeTotal);
        $this->assertSame('0.035', $surcharge->rate);
    }

    public function test_construct_withMoneyArrays_createsInstance(): void
    {
        // Arrange & Act
        $surcharge = new Surcharge(
            unadjustedTotal: Money::GBP(5000),
            unadjustedRefundableTotal: Money::GBP(5000),
            surchargeTotal: Money::GBP(175),
            rate: '0.035'
        );

        // Assert
        $this->assertInstanceOf(Money::class, $surcharge->unadjustedTotal);
        $this->assertSame('5000', $surcharge->unadjustedTotal->getAmount());
        $this->assertSame('GBP', $surcharge->unadjustedTotal->getCurrency()->getCode());

        $this->assertInstanceOf(Money::class, $surcharge->unadjustedRefundableTotal);
        $this->assertSame('5000', $surcharge->unadjustedRefundableTotal->getAmount());

        $this->assertInstanceOf(Money::class, $surcharge->surchargeTotal);
        $this->assertSame('175', $surcharge->surchargeTotal->getAmount());

        $this->assertSame('0.035', $surcharge->rate);
    }

    public function test_construct_withNoProperties_createsInstance(): void
    {
        // Arrange & Act
        $surcharge = new Surcharge();

        // Assert
        $this->assertNull($surcharge->unadjustedTotal);
        $this->assertNull($surcharge->unadjustedRefundableTotal);
        $this->assertNull($surcharge->surchargeTotal);
        $this->assertNull($surcharge->rate);
    }

    public function test_fromArray_withAllProperties_createsInstance(): void
    {
        // Arrange
        $data = [
            'unadjustedTotal' => ['amount' => '200.00', 'currencyCode' => 'EUR'],
            'unadjustedRefundableTotal' => ['amount' => '200.00', 'currencyCode' => 'EUR'],
            'surchargeTotal' => ['amount' => '7.00', 'currencyCode' => 'EUR'],
            'rate' => '0.035',
        ];

        // Act
        $surcharge = Surcharge::fromData($data);

        // Assert
        $this->assertInstanceOf(Money::class, $surcharge->unadjustedTotal);
        $this->assertSame('20000', $surcharge->unadjustedTotal->getAmount());
        $this->assertSame('EUR', $surcharge->unadjustedTotal->getCurrency()->getCode());

        $this->assertInstanceOf(Money::class, $surcharge->unadjustedRefundableTotal);
        $this->assertSame('20000', $surcharge->unadjustedRefundableTotal->getAmount());

        $this->assertInstanceOf(Money::class, $surcharge->surchargeTotal);
        $this->assertSame('700', $surcharge->surchargeTotal->getAmount());

        $this->assertSame('0.035', $surcharge->rate);
    }

    public function test_fromArray_withMissingProperties_createsInstanceWithNulls(): void
    {
        // Arrange
        $data = [
            'rate' => '0.04',
        ];

        // Act
        $surcharge = Surcharge::fromData($data);

        // Assert
        $this->assertNull($surcharge->unadjustedTotal);
        $this->assertNull($surcharge->unadjustedRefundableTotal);
        $this->assertNull($surcharge->surchargeTotal);
        $this->assertSame('0.04', $surcharge->rate);
    }

    public function test_toArray_withAllProperties_returnsCompleteArray(): void
    {
        // Arrange
        $surcharge = new Surcharge(
            unadjustedTotal: Money::USD(15000),
            unadjustedRefundableTotal: Money::USD(15000),
            surchargeTotal: Money::USD(525),
            rate: '0.035'
        );

        // Act
        $result = $surcharge->toData();

        // Assert
        $this->assertSame([
            'unadjustedTotal' => ['amount' => '150.00', 'currencyCode' => 'USD'],
            'unadjustedRefundableTotal' => ['amount' => '150.00', 'currencyCode' => 'USD'],
            'surchargeTotal' => ['amount' => '5.25', 'currencyCode' => 'USD'],
            'rate' => '0.035',
        ], $result);
    }

    public function test_toArray_withNullProperties_excludesNullValues(): void
    {
        // Arrange
        $surcharge = new Surcharge(
            unadjustedTotal: Money::GBP(10000),
            rate: '0.03'
        );

        // Act
        $result = $surcharge->toData();

        // Assert
        $this->assertArrayHasKey('unadjustedTotal', $result);
        $this->assertArrayHasKey('rate', $result);
        $this->assertArrayNotHasKey('unadjustedRefundableTotal', $result);
        $this->assertArrayNotHasKey('surchargeTotal', $result);
    }

    public function test_toArray_roundTrip_preservesData(): void
    {
        // Arrange
        $originalData = [
            'unadjustedTotal' => ['amount' => '75.00', 'currencyCode' => 'EUR'],
            'unadjustedRefundableTotal' => ['amount' => '75.00', 'currencyCode' => 'EUR'],
            'surchargeTotal' => ['amount' => '2.63', 'currencyCode' => 'EUR'],
            'rate' => '0.035',
        ];
        $surcharge = Surcharge::fromData($originalData);

        // Act
        $result = $surcharge->toData();

        // Assert
        $this->assertSame($originalData, $result);
    }
}
