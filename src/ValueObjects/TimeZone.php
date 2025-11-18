<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\ValueObjects;

use Academe\Elavon\Epg\Psr7\Contracts\ValueObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use DateTimeZone;

/**
 * Time Zone value object.
 *
 * Represents a validated IANA Time Zone Database name.
 * Examples: "Europe/London", "America/New_York", "UTC"
 * Serializes to a simple string value.
 */
class TimeZone implements ValueObject
{
    /**
     * @param string $timezone The IANA timezone identifier
     */
    public function __construct(
        public readonly string $timezone,
    ) {
        $this->validate();
    }

    /**
     * Creates a TimeZone instance from JSON-compatible data.
     *
     * @param mixed $data String timezone identifier
     *
     * @throws InvalidArgumentException When data is invalid
     */
    public static function fromData(mixed $data): static
    {
        if (!is_string($data)) {
            throw new InvalidArgumentException('Timezone must be a string');
        }

        return new self(timezone: $data);
    }

    /**
     * Creates a TimeZone from a DateTimeZone object.
     *
     * @param DateTimeZone $dateTimeZone
     * @return static
     */
    public static function fromDateTimeZone(DateTimeZone $dateTimeZone): static
    {
        return new self(timezone: $dateTimeZone->getName());
    }

    /**
     * Converts the TimeZone to JSON-compatible data.
     *
     * Returns a simple string representation.
     *
     * @return string
     */
    public function toData(): mixed
    {
        return $this->timezone;
    }

    /**
     * Validates the timezone identifier.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        if (empty($this->timezone)) {
            throw new InvalidArgumentException('Timezone cannot be empty');
        }

        // Validate against IANA timezone database using DateTimeZone
        try {
            new DateTimeZone($this->timezone);
        } catch (\Exception $e) {
            throw new InvalidArgumentException(
                "Invalid timezone identifier: '{$this->timezone}'. Expected IANA Time Zone Database name (e.g., 'Europe/London', 'America/New_York', 'UTC').",
                0,
                $e
            );
        }
    }

    /**
     * Returns the timezone as a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->timezone;
    }

    /**
     * Converts to a DateTimeZone object.
     *
     * @return DateTimeZone
     */
    public function toDateTimeZone(): DateTimeZone
    {
        return new DateTimeZone($this->timezone);
    }

    /**
     * Gets the timezone offset in seconds for a given datetime.
     *
     * @param \DateTimeInterface|null $dateTime DateTime to calculate offset for (defaults to now)
     * @return int Offset in seconds from UTC
     */
    public function getOffset(?\DateTimeInterface $dateTime = null): int
    {
        $dateTime = $dateTime ?? new \DateTimeImmutable('now', new DateTimeZone('UTC'));
        $tz = $this->toDateTimeZone();

        return $tz->getOffset($dateTime);
    }

    /**
     * Gets the timezone offset as a formatted string (e.g., "+01:00", "-05:00").
     *
     * @param \DateTimeInterface|null $dateTime DateTime to calculate offset for (defaults to now)
     * @return string Formatted offset string
     */
    public function getOffsetFormatted(?\DateTimeInterface $dateTime = null): string
    {
        $offset = $this->getOffset($dateTime);
        $hours = intdiv(abs($offset), 3600);
        $minutes = intdiv(abs($offset) % 3600, 60);
        $sign = $offset >= 0 ? '+' : '-';

        return sprintf('%s%02d:%02d', $sign, $hours, $minutes);
    }
}
