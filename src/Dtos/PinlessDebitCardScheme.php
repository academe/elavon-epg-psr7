<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\CardScheme;
use Money\Money;

/**
 * Pinless Debit Card Scheme data transfer object.
 *
 * Represents pinless debit configuration for a specific card scheme.
 * Enables in-person debit payments without requesting a PIN when the
 * transaction amount is less than the threshold.
 *
 * All properties are read-only and only present in API responses.
 */
class PinlessDebitCardScheme implements DataTransferObject
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
            'enum' => ['cardScheme'],
            'boolean' => ['isEnabled'],
            'money' => ['threshold'],
        ];
    }

    public function __construct(
        public readonly ?CardScheme $cardScheme = null,
        public readonly ?bool $isEnabled = null,
        public readonly ?Money $threshold = null,
    ) {
    }
}
