<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\ValueObjects;

use Academe\Elavon\Epg\Psr7\Contracts\ValueObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * DateTime value object.
 *
 * Represents an immutable date/time.
 * Serializes to ISO 8601 format (ATOM).
 */
class DateTime implements ValueObject
{
    /**
     * @param DateTimeImmutable $dateTime The immutable date/time
     */
    public function __construct(
        public readonly DateTimeImmutable $dateTime,
    ) {
    }

    /**
     * Creates a DateTime instance from JSON-compatible data.
     *
     * Accepts ISO 8601 / RFC 3339 formatted string.
     *
     * @param mixed $data ISO 8601 formatted datetime string
     *
     * @throws InvalidArgumentException When data is invalid
     */
    public static function fromData(mixed $data): static
    {
        if (!is_string($data)) {
            throw new InvalidArgumentException('DateTime must be a string');
        }

        if (empty($data)) {
            throw new InvalidArgumentException('DateTime cannot be empty');
        }

        try {
            $dateTime = new DateTimeImmutable($data);
        } catch (\Exception $e) {
            throw new InvalidArgumentException(
                "Invalid datetime format: '{$data}'. Expected ISO 8601 format.",
                0,
                $e
            );
        }

        return new self($dateTime);
    }

    /**
     * Creates a DateTime from a DateTimeInterface.
     *
     * @param DateTimeInterface $dateTime
     * @return static
     */
    public static function fromDateTime(DateTimeInterface $dateTime): static
    {
        if ($dateTime instanceof DateTimeImmutable) {
            return new self($dateTime);
        }

        return new self(DateTimeImmutable::createFromMutable($dateTime));
    }

    /**
     * Creates a DateTime for the current moment.
     *
     * @return static
     */
    public static function now(): static
    {
        return new self(new DateTimeImmutable());
    }

    /**
     * Converts the DateTime to JSON-compatible data.
     *
     * Returns ISO 8601 / RFC 3339 formatted string (ATOM format).
     *
     * @return string
     */
    public function toData(): mixed
    {
        return $this->dateTime->format(DateTimeInterface::ATOM);
    }

    /**
     * Returns the datetime as a string in ISO 8601 format.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toData();
    }

    /**
     * Formats the datetime using a custom format.
     *
     * @param string $format PHP date format string
     * @return string
     */
    public function format(string $format): string
    {
        return $this->dateTime->format($format);
    }

    /**
     * Checks if this datetime is before another.
     *
     * @param DateTime $other
     * @return bool
     */
    public function isBefore(DateTime $other): bool
    {
        return $this->dateTime < $other->dateTime;
    }

    /**
     * Checks if this datetime is after another.
     *
     * @param DateTime $other
     * @return bool
     */
    public function isAfter(DateTime $other): bool
    {
        return $this->dateTime > $other->dateTime;
    }

    /**
     * Checks if this datetime equals another.
     *
     * @param DateTime $other
     * @return bool
     */
    public function equals(DateTime $other): bool
    {
        return $this->dateTime == $other->dateTime;
    }
}
