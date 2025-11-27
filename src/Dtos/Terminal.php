<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\TerminalType;

/**
 * Terminal data transfer object.
 *
 * Information about a terminal (hardware or software) used for payment processing.
 * Terminals are read-only via the API.
 *
 * All properties are read-only and only present in API responses.
 */
class Terminal implements DataTransferObject
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
            'string' => [
                'id', 'merchant', 'processorAccount', 'processorReference',
                'hostLastUpdatedEmvKeysAt', 'provisionedAt', 'transactedAt',
            ],
            'enum' => ['terminalType'],
        ];
    }

    /**
     * @param string|null $id Unique identifier assigned to the terminal
     * @param string|null $merchant Merchant Resource URL (suppressed when public API key is used)
     * @param string|null $processorAccount ProcessorAccount Resource URL
     * @param string|null $processorReference Reference assigned by the processor
     * @param TerminalType|string|null $terminalType Terminal type (HARDWARE or SOFTWARE)
     * @param string|null $hostLastUpdatedEmvKeysAt Most recent date host updated their EMV keys
     * @param string|null $provisionedAt Terminal provision timestamp
     * @param string|null $transactedAt Terminal last transaction timestamp
     */
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $merchant = null,
        public readonly ?string $processorAccount = null,
        public readonly ?string $processorReference = null,
        public readonly ?TerminalType $terminalType = null,
        public readonly ?string $hostLastUpdatedEmvKeysAt = null,
        public readonly ?string $provisionedAt = null,
        public readonly ?string $transactedAt = null,
    ) {
    }
}
