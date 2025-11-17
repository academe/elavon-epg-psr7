<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\ValueObjects;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\ValueObjects\DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

class DateTimeTest extends TestCase
{
    public function test_construct_withDateTimeImmutable_createsInstance(): void
    {
        $phpDateTime = new DateTimeImmutable('2025-01-16T10:30:00+00:00');
        $dateTime = new DateTime($phpDateTime);

        $this->assertSame($phpDateTime, $dateTime->dateTime);
    }

    public function test_fromData_withValidIso8601String_createsInstance(): void
    {
        $dateTime = DateTime::fromData('2025-01-16T10:30:00+00:00');

        $this->assertSame('2025-01-16T10:30:00+00:00', $dateTime->toData());
    }

    public function test_fromData_withNonString_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('DateTime must be a string');

        DateTime::fromData(['datetime' => '2025-01-16T10:30:00+00:00']);
    }

    public function test_fromData_withEmptyString_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('DateTime cannot be empty');

        DateTime::fromData('');
    }

    public function test_fromData_withInvalidFormat_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid datetime format');

        DateTime::fromData('not-a-datetime');
    }

    public function test_fromDateTime_withDateTimeImmutable_createsInstance(): void
    {
        $phpDateTime = new DateTimeImmutable('2025-01-16T10:30:00+00:00');
        $dateTime = DateTime::fromDateTime($phpDateTime);

        $this->assertSame($phpDateTime, $dateTime->dateTime);
    }

    public function test_fromDateTime_withMutableDateTime_createsImmutableInstance(): void
    {
        $mutableDateTime = new \DateTime('2025-01-16T10:30:00+00:00');
        $dateTime = DateTime::fromDateTime($mutableDateTime);

        $this->assertInstanceOf(DateTimeImmutable::class, $dateTime->dateTime);
        $this->assertSame('2025-01-16T10:30:00+00:00', $dateTime->toData());
    }

    public function test_now_createsCurrentDateTime(): void
    {
        $before = new DateTimeImmutable();
        $dateTime = DateTime::now();
        $after = new DateTimeImmutable();

        $this->assertGreaterThanOrEqual($before, $dateTime->dateTime);
        $this->assertLessThanOrEqual($after, $dateTime->dateTime);
    }

    public function test_toData_returnsIso8601String(): void
    {
        $dateTime = DateTime::fromData('2025-01-16T10:30:00+00:00');

        $result = $dateTime->toData();

        $this->assertIsString($result);
        $this->assertSame('2025-01-16T10:30:00+00:00', $result);
    }

    public function test_toString_returnsIso8601String(): void
    {
        $dateTime = DateTime::fromData('2025-01-16T10:30:00+00:00');

        $this->assertSame('2025-01-16T10:30:00+00:00', (string) $dateTime);
    }

    public function test_format_withCustomFormat_returnsFormattedString(): void
    {
        $dateTime = DateTime::fromData('2025-01-16T10:30:00+00:00');

        $this->assertSame('2025-01-16', $dateTime->format('Y-m-d'));
        $this->assertSame('10:30:00', $dateTime->format('H:i:s'));
        $this->assertSame('16/01/2025', $dateTime->format('d/m/Y'));
    }

    public function test_isBefore_withEarlierDateTime_returnsTrue(): void
    {
        $earlier = DateTime::fromData('2025-01-15T10:00:00+00:00');
        $later = DateTime::fromData('2025-01-16T10:00:00+00:00');

        $this->assertTrue($earlier->isBefore($later));
    }

    public function test_isBefore_withLaterDateTime_returnsFalse(): void
    {
        $earlier = DateTime::fromData('2025-01-15T10:00:00+00:00');
        $later = DateTime::fromData('2025-01-16T10:00:00+00:00');

        $this->assertFalse($later->isBefore($earlier));
    }

    public function test_isAfter_withLaterDateTime_returnsTrue(): void
    {
        $earlier = DateTime::fromData('2025-01-15T10:00:00+00:00');
        $later = DateTime::fromData('2025-01-16T10:00:00+00:00');

        $this->assertTrue($later->isAfter($earlier));
    }

    public function test_isAfter_withEarlierDateTime_returnsFalse(): void
    {
        $earlier = DateTime::fromData('2025-01-15T10:00:00+00:00');
        $later = DateTime::fromData('2025-01-16T10:00:00+00:00');

        $this->assertFalse($earlier->isAfter($later));
    }

    public function test_equals_withSameDateTime_returnsTrue(): void
    {
        $dt1 = DateTime::fromData('2025-01-16T10:30:00+00:00');
        $dt2 = DateTime::fromData('2025-01-16T10:30:00+00:00');

        $this->assertTrue($dt1->equals($dt2));
    }

    public function test_equals_withDifferentDateTime_returnsFalse(): void
    {
        $dt1 = DateTime::fromData('2025-01-16T10:30:00+00:00');
        $dt2 = DateTime::fromData('2025-01-16T10:31:00+00:00');

        $this->assertFalse($dt1->equals($dt2));
    }

    /**
     * @dataProvider validDateTimeProvider
     */
    public function test_fromData_withValidFormats_succeeds(string $input): void
    {
        $dateTime = DateTime::fromData($input);

        $this->assertInstanceOf(DateTime::class, $dateTime);
        $this->assertInstanceOf(DateTimeImmutable::class, $dateTime->dateTime);
    }

    public static function validDateTimeProvider(): array
    {
        return [
            'ISO 8601 with timezone' => ['2025-01-16T10:30:00+00:00'],
            'ISO 8601 UTC' => ['2025-01-16T10:30:00Z'],
            'ISO 8601 with offset' => ['2025-01-16T10:30:00+05:30'],
            'RFC 3339' => ['2025-01-16T10:30:00.123456+00:00'],
            'Simple date' => ['2025-01-16'],
            'DateTime with space' => ['2025-01-16 10:30:00'],
        ];
    }

    public function test_serializationRoundTrip_preservesValue(): void
    {
        $original = DateTime::fromData('2025-01-16T10:30:00+00:00');
        $data = $original->toData();
        $restored = DateTime::fromData($data);

        $this->assertTrue($original->equals($restored));
        $this->assertSame('2025-01-16T10:30:00+00:00', $data);
    }

    public function test_serializationRoundTrip_withDifferentTimezone_normalizes(): void
    {
        // Create datetime in different timezone
        $original = DateTime::fromData('2025-01-16T10:30:00+05:30');
        $data = $original->toData();
        $restored = DateTime::fromData($data);

        // Should be equal (same moment in time)
        $this->assertTrue($original->equals($restored));
    }
}
