<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\ValueObjects;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\ValueObjects\TimeZone;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

class TimeZoneTest extends TestCase
{
    public function test_construct_withValidTimezone_createsInstance(): void
    {
        $tz = new TimeZone('Europe/London');

        $this->assertSame('Europe/London', $tz->timezone);
    }

    public function test_fromData_withValidString_createsInstance(): void
    {
        $tz = TimeZone::fromData('America/New_York');

        $this->assertSame('America/New_York', $tz->timezone);
    }

    public function test_fromData_withNonString_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Timezone must be a string');

        TimeZone::fromData(['timezone' => 'UTC']);
    }

    public function test_fromDateTimeZone_createsInstance(): void
    {
        $phpTz = new DateTimeZone('Asia/Tokyo');
        $tz = TimeZone::fromDateTimeZone($phpTz);

        $this->assertSame('Asia/Tokyo', $tz->timezone);
    }

    public function test_toData_returnsString(): void
    {
        $tz = new TimeZone('UTC');

        $this->assertSame('UTC', $tz->toData());
    }

    public function test_toString_returnsTimezone(): void
    {
        $tz = new TimeZone('Europe/Berlin');

        $this->assertSame('Europe/Berlin', (string) $tz);
    }

    public function test_construct_withEmptyString_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Timezone cannot be empty');

        new TimeZone('');
    }

    public function test_construct_withInvalidFormat_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid timezone identifier: 'Not/A/Timezone'");

        new TimeZone('Not/A/Timezone');
    }

    /**
     * @dataProvider validTimezoneProvider
     */
    public function test_construct_withValidTimezones_succeeds(string $timezone): void
    {
        $tzObject = new TimeZone($timezone);

        $this->assertSame($timezone, $tzObject->timezone);
    }

    public static function validTimezoneProvider(): array
    {
        return [
            'UTC' => ['UTC'],
            'Europe/London' => ['Europe/London'],
            'Europe/Berlin' => ['Europe/Berlin'],
            'America/New_York' => ['America/New_York'],
            'America/Los_Angeles' => ['America/Los_Angeles'],
            'Asia/Tokyo' => ['Asia/Tokyo'],
            'Australia/Sydney' => ['Australia/Sydney'],
            'Pacific/Auckland' => ['Pacific/Auckland'],
            'Africa/Cairo' => ['Africa/Cairo'],
            'America/Sao_Paulo' => ['America/Sao_Paulo'],
        ];
    }

    /**
     * @dataProvider invalidTimezoneProvider
     */
    public function test_construct_withInvalidTimezones_throwsException(string $timezone): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid timezone identifier');

        new TimeZone($timezone);
    }

    public static function invalidTimezoneProvider(): array
    {
        return [
            'invalid format' => ['Not/A/Timezone'],
            'random text' => ['random'],
            'numeric' => ['123'],
            'partial' => ['Europe/'],
        ];
    }

    public function test_toDateTimeZone_returnsDateTimeZone(): void
    {
        $tz = new TimeZone('Europe/Paris');
        $phpTz = $tz->toDateTimeZone();

        $this->assertInstanceOf(DateTimeZone::class, $phpTz);
        $this->assertSame('Europe/Paris', $phpTz->getName());
    }

    public function test_getOffset_forUTC_returnsZero(): void
    {
        $tz = new TimeZone('UTC');
        $offset = $tz->getOffset();

        $this->assertSame(0, $offset);
    }

    public function test_getOffset_forTimezoneWithOffset_returnsCorrectOffset(): void
    {
        $tz = new TimeZone('Europe/London');
        $winterDate = new DateTimeImmutable('2025-01-15 12:00:00', new DateTimeZone('UTC'));
        $offset = $tz->getOffset($winterDate);

        // London is UTC+0 in winter (no DST)
        $this->assertSame(0, $offset);
    }

    public function test_getOffset_accountsForDST(): void
    {
        $tz = new TimeZone('America/New_York');

        // Winter: EST (UTC-5)
        $winterDate = new DateTimeImmutable('2025-01-15 12:00:00', new DateTimeZone('UTC'));
        $winterOffset = $tz->getOffset($winterDate);
        $this->assertSame(-18000, $winterOffset); // -5 hours in seconds

        // Summer: EDT (UTC-4)
        $summerDate = new DateTimeImmutable('2025-07-15 12:00:00', new DateTimeZone('UTC'));
        $summerOffset = $tz->getOffset($summerDate);
        $this->assertSame(-14400, $summerOffset); // -4 hours in seconds
    }

    public function test_getOffsetFormatted_forUTC_returnsZeroOffset(): void
    {
        $tz = new TimeZone('UTC');
        $formatted = $tz->getOffsetFormatted();

        $this->assertSame('+00:00', $formatted);
    }

    public function test_getOffsetFormatted_forPositiveOffset_returnsCorrectFormat(): void
    {
        $tz = new TimeZone('Asia/Tokyo');
        $formatted = $tz->getOffsetFormatted();

        // Tokyo is UTC+9
        $this->assertSame('+09:00', $formatted);
    }

    public function test_getOffsetFormatted_forNegativeOffset_returnsCorrectFormat(): void
    {
        $tz = new TimeZone('America/New_York');
        $winterDate = new DateTimeImmutable('2025-01-15 12:00:00', new DateTimeZone('UTC'));
        $formatted = $tz->getOffsetFormatted($winterDate);

        // New York is UTC-5 in winter
        $this->assertSame('-05:00', $formatted);
    }

    public function test_getOffsetFormatted_withHalfHourOffset_returnsCorrectFormat(): void
    {
        $tz = new TimeZone('Asia/Kolkata');
        $formatted = $tz->getOffsetFormatted();

        // India is UTC+5:30
        $this->assertSame('+05:30', $formatted);
    }

    public function test_serializationRoundTrip_preservesValue(): void
    {
        $original = new TimeZone('Europe/Berlin');
        $data = $original->toData();
        $restored = TimeZone::fromData($data);

        $this->assertSame($original->timezone, $restored->timezone);
        $this->assertSame('Europe/Berlin', $data);
    }

    public function test_roundTripWithDateTimeZone_preservesValue(): void
    {
        $phpTz = new DateTimeZone('Australia/Sydney');
        $tz = TimeZone::fromDateTimeZone($phpTz);
        $restoredPhpTz = $tz->toDateTimeZone();

        $this->assertSame('Australia/Sydney', $restoredPhpTz->getName());
    }
}
