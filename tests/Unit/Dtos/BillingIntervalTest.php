<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\BillingInterval;
use Academe\Elavon\Epg\Psr7\Enums\TimeUnit;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for BillingInterval DTO.
 */
class BillingIntervalTest extends TestCase
{
    public function test_construct_withValidData_createsInstance(): void
    {
        // Arrange & Act
        $interval = new BillingInterval(
            timeUnit: 'month',
            count: 1
        );

        // Assert
        $this->assertSame('month', $interval->timeUnit);
        $this->assertSame(1, $interval->count);
    }

    public function test_construct_withTimeUnitEnum_createsInstance(): void
    {
        // Arrange & Act
        $interval = new BillingInterval(
            timeUnit: TimeUnit::MONTH,
            count: 3
        );

        // Assert
        $this->assertSame(TimeUnit::MONTH, $interval->timeUnit);
        $this->assertSame(3, $interval->count);
    }

    public function test_construct_withInvalidTimeUnit_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Time unit must be one of: day, week, month, year');

        // Act
        new BillingInterval(
            timeUnit: 'invalid',
            count: 1
        );
    }

    public function test_construct_withZeroCount_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Billing interval count must be at least 1');

        // Act
        new BillingInterval(
            timeUnit: 'month',
            count: 0
        );
    }

    public function test_construct_withNegativeCount_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Billing interval count must be at least 1');

        // Act
        new BillingInterval(
            timeUnit: 'week',
            count: -1
        );
    }

    public function test_toData_withStringTimeUnit_returnsArray(): void
    {
        // Arrange
        $interval = new BillingInterval(timeUnit: 'year', count: 2);

        // Act
        $data = $interval->toData();

        // Assert
        $this->assertSame([
            'timeUnit' => 'year',
            'count' => 2,
        ], $data);
    }

    public function test_toData_withEnumTimeUnit_returnsArrayWithStringValue(): void
    {
        // Arrange
        $interval = new BillingInterval(timeUnit: TimeUnit::DAY, count: 7);

        // Act
        $data = $interval->toData();

        // Assert
        $this->assertSame([
            'timeUnit' => 'day',
            'count' => 7,
        ], $data);
    }

    public function test_fromData_createsInstance(): void
    {
        // Arrange
        $data = [
            'timeUnit' => 'week',
            'count' => 4,
        ];

        // Act
        $interval = BillingInterval::fromData($data);

        // Assert
        $this->assertSame('week', $interval->timeUnit);
        $this->assertSame(4, $interval->count);
    }
}
