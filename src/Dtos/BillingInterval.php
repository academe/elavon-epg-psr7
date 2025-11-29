<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\TimeUnit;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * Billing Interval data transfer object.
 *
 * Defines time period between recurring bills.
 */
class BillingInterval implements DataTransferObject
{
    use SerializesData;

    /**
     * Get property type definitions for this DTO.
     *
     * @return array<string, array<string>>
     */
    public static function getPropertyTypes(): array
    {
        return [
            'string' => ['timeUnit'],
            'int' => ['count'],
        ];
    }

    public function __construct(
        public readonly string|TimeUnit $timeUnit,
        public readonly int $count,
    ) {
        $this->validate();
    }

    /**
     * Validates billing interval data.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // Validate count is at least 1
        if ($this->count < 1) {
            throw new InvalidArgumentException('Billing interval count must be at least 1');
        }

        // Validate timeUnit is a valid TimeUnit value
        if (is_string($this->timeUnit)) {
            try {
                TimeUnit::from($this->timeUnit);
            } catch (\ValueError $e) {
                throw new InvalidArgumentException(
                    'Time unit must be one of: day, week, month, year',
                    previous: $e
                );
            }
        }
    }

    /**
     * Serializes this object to an array for API requests.
     *
     * @return array<string, mixed>
     */
    public function toData(): array
    {
        return [
            'timeUnit' => $this->timeUnit instanceof TimeUnit ? $this->timeUnit->value : $this->timeUnit,
            'count' => $this->count,
        ];
    }
}
