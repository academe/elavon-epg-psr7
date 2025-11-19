<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;

/**
 * AutoSettleAt data transfer object.
 *
 * Defines the time and timezone for automatic settlement.
 * All properties are read-only.
 */
class AutoSettleAt implements DataTransferObject
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
            'string' => ['time', 'timeZoneId'],
        ];
    }

    /**
     * @param string|null $time Time in 24-hour format (e.g., "23:00")
     * @param string|null $timeZoneId IANA Time Zone Database name (e.g., "Europe/Berlin")
     */
    public function __construct(
        public readonly ?string $time = null,
        public readonly ?string $timeZoneId = null,
    ) {
    }
}
