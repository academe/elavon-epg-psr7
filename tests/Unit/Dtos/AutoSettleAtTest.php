<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\AutoSettleAt;
use PHPUnit\Framework\TestCase;

/**
 * Tests for AutoSettleAt DTO.
 */
class AutoSettleAtTest extends TestCase
{
    public function test_construct_withMinimalFields_createsInstance(): void
    {
        // Arrange & Act
        $autoSettleAt = new AutoSettleAt();

        // Assert
        $this->assertNull($autoSettleAt->time);
        $this->assertNull($autoSettleAt->timeZoneId);
    }

    public function test_construct_withAllFields_createsInstance(): void
    {
        // Arrange & Act
        $autoSettleAt = new AutoSettleAt(
            time: '23:00',
            timeZoneId: 'Europe/Berlin'
        );

        // Assert
        $this->assertSame('23:00', $autoSettleAt->time);
        $this->assertSame('Europe/Berlin', $autoSettleAt->timeZoneId);
    }

    public function test_fromData_withMinimalData_createsInstance(): void
    {
        // Arrange
        $data = [];

        // Act
        $autoSettleAt = AutoSettleAt::fromData($data);

        // Assert
        $this->assertNull($autoSettleAt->time);
        $this->assertNull($autoSettleAt->timeZoneId);
    }

    public function test_fromData_withFullData_createsInstance(): void
    {
        // Arrange
        $data = [
            'time' => '02:30',
            'timeZoneId' => 'America/New_York',
        ];

        // Act
        $autoSettleAt = AutoSettleAt::fromData($data);

        // Assert
        $this->assertSame('02:30', $autoSettleAt->time);
        $this->assertSame('America/New_York', $autoSettleAt->timeZoneId);
    }

    public function test_toData_withMinimalData_returnsArray(): void
    {
        // Arrange
        $autoSettleAt = new AutoSettleAt();

        // Act
        $array = $autoSettleAt->toData();

        // Assert
        $this->assertSame([], $array);
    }

    public function test_toData_withFullData_returnsArray(): void
    {
        // Arrange
        $autoSettleAt = new AutoSettleAt(
            time: '18:45',
            timeZoneId: 'Asia/Tokyo'
        );

        // Act
        $array = $autoSettleAt->toData();

        // Assert
        $this->assertArrayHasKey('time', $array);
        $this->assertSame('18:45', $array['time']);
        $this->assertArrayHasKey('timeZoneId', $array);
        $this->assertSame('Asia/Tokyo', $array['timeZoneId']);
    }

    public function test_roundTrip_fromDataToData_preservesData(): void
    {
        // Arrange
        $originalData = [
            'time' => '12:00',
            'timeZoneId' => 'Europe/London',
        ];

        // Act
        $autoSettleAt = AutoSettleAt::fromData($originalData);
        $resultData = $autoSettleAt->toData();

        // Assert
        $this->assertSame($originalData, $resultData);
    }

    public function test_properties_areReadonly(): void
    {
        // Arrange
        $autoSettleAt = new AutoSettleAt(time: '10:00');

        // Act & Assert
        $reflection = new \ReflectionProperty($autoSettleAt, 'time');
        $this->assertTrue($reflection->isReadOnly());

        $reflection = new \ReflectionProperty($autoSettleAt, 'timeZoneId');
        $this->assertTrue($reflection->isReadOnly());
    }
}
