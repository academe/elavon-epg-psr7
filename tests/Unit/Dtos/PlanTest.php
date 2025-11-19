<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\BillingInterval;
use Academe\Elavon\Epg\Psr7\Dtos\Plan;
use Academe\Elavon\Epg\Psr7\Dtos\ShopperStatement;
use Academe\Elavon\Epg\Psr7\Enums\Currency;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Plan DTO.
 */
class PlanTest extends TestCase
{
    public function test_construct_withMinimalFields_createsInstance(): void
    {
        // Arrange & Act
        $plan = new Plan(
            name: 'Monthly License',
            billingInterval: ['timeUnit' => 'month', 'count' => 1],
            total: ['amount' => '29.99', 'currencyCode' => 'USD']
        );

        // Assert
        $this->assertSame('Monthly License', $plan->name);
        $this->assertInstanceOf(BillingInterval::class, $plan->billingInterval);
        $this->assertInstanceOf(Money::class, $plan->total);
        $this->assertSame('29.99', $plan->total->amount);
        $this->assertNull($plan->id);
        $this->assertNull($plan->description);
    }

    public function test_construct_withAllFields_createsInstance(): void
    {
        // Arrange & Act
        $plan = new Plan(
            href: 'https://api.example.com/plans/plan123',
            id: 'plan123',
            createdAt: '2025-11-19T10:00:00Z',
            modifiedAt: '2025-11-19T11:00:00Z',
            merchant: 'https://api.example.com/merchants/m123',
            planList: 'https://api.example.com/plan-lists/pl123',
            name: 'Annual Subscription',
            description: 'Yearly billing plan',
            billingInterval: ['timeUnit' => 'year', 'count' => 1],
            total: ['amount' => '299.99', 'currencyCode' => 'USD'],
            salesTax: ['amount' => '30.00', 'currencyCode' => 'USD'],
            billCount: 12,
            initialTotal: ['amount' => '0.00', 'currencyCode' => 'USD'],
            initialSalesTax: ['amount' => '0.00', 'currencyCode' => 'USD'],
            initialTotalBillCount: 1,
            shopperStatement: ['name' => 'ACME Corp'],
            isSubscribable: true,
            customReference: 'PLAN-001',
            customFields: ['tier' => 'premium'],
        );

        // Assert
        $this->assertSame('plan123', $plan->id);
        $this->assertSame('Annual Subscription', $plan->name);
        $this->assertSame('Yearly billing plan', $plan->description);
        $this->assertSame(12, $plan->billCount);
        $this->assertSame(1, $plan->initialTotalBillCount);
        $this->assertTrue($plan->isSubscribable);
        $this->assertSame('PLAN-001', $plan->customReference);
        $this->assertInstanceOf(ShopperStatement::class, $plan->shopperStatement);
    }

    public function test_construct_withMoneyObjects_createsInstance(): void
    {
        // Arrange
        $total = new Money('49.99', Currency::EUR);
        $salesTax = new Money('5.00', Currency::EUR);

        // Act
        $plan = new Plan(
            name: 'Test Plan',
            billingInterval: ['timeUnit' => 'month', 'count' => 1],
            total: $total,
            salesTax: $salesTax
        );

        // Assert
        $this->assertSame($total, $plan->total);
        $this->assertSame($salesTax, $plan->salesTax);
    }

    public function test_construct_withBillingIntervalObject_createsInstance(): void
    {
        // Arrange
        $interval = new BillingInterval(timeUnit: 'week', count: 2);

        // Act
        $plan = new Plan(
            name: 'Bi-weekly Plan',
            billingInterval: $interval,
            total: ['amount' => '10.00', 'currencyCode' => 'USD']
        );

        // Assert
        $this->assertSame($interval, $plan->billingInterval);
    }

    public function test_construct_withTooLongName_throwsException(): void
    {
        // Arrange
        $longName = str_repeat('a', 256);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Name must not exceed 255 characters');

        // Act
        new Plan(
            name: $longName,
            billingInterval: ['timeUnit' => 'month', 'count' => 1],
            total: ['amount' => '10.00', 'currencyCode' => 'USD']
        );
    }

    public function test_construct_withTooLongDescription_throwsException(): void
    {
        // Arrange
        $longDescription = str_repeat('a', 256);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Description must not exceed 255 characters');

        // Act
        new Plan(
            name: 'Test',
            description: $longDescription,
            billingInterval: ['timeUnit' => 'month', 'count' => 1],
            total: ['amount' => '10.00', 'currencyCode' => 'USD']
        );
    }

    public function test_construct_withZeroBillCount_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Bill count must be at least 1');

        // Act
        new Plan(
            name: 'Test',
            billingInterval: ['timeUnit' => 'month', 'count' => 1],
            total: ['amount' => '10.00', 'currencyCode' => 'USD'],
            billCount: 0
        );
    }

    public function test_construct_withNegativeInitialTotalBillCount_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Initial total bill count must be at least 0');

        // Act
        new Plan(
            name: 'Test',
            billingInterval: ['timeUnit' => 'month', 'count' => 1],
            total: ['amount' => '10.00', 'currencyCode' => 'USD'],
            initialTotalBillCount: -1
        );
    }

    public function test_fromData_createsInstance(): void
    {
        // Arrange
        $data = [
            'name' => 'Test Plan',
            'billingInterval' => ['timeUnit' => 'month', 'count' => 1],
            'total' => ['amount' => '19.99', 'currencyCode' => 'GBP'],
        ];

        // Act
        $plan = Plan::fromData($data);

        // Assert
        $this->assertSame('Test Plan', $plan->name);
        $this->assertSame('19.99', $plan->total->amount);
        $this->assertSame(Currency::GBP, $plan->total->currency);
    }

    public function test_toData_returnsArrayWithoutNullValues(): void
    {
        // Arrange
        $plan = new Plan(
            name: 'Basic Plan',
            description: 'A basic plan',
            billingInterval: ['timeUnit' => 'month', 'count' => 1],
            total: ['amount' => '9.99', 'currencyCode' => 'USD']
        );

        // Act
        $data = $plan->toData();

        // Assert
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('description', $data);
        $this->assertArrayHasKey('billingInterval', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayNotHasKey('id', $data);
        $this->assertArrayNotHasKey('href', $data);
    }
}
