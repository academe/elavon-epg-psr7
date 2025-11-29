<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\BatchState;

/**
 * Batch data transfer object.
 *
 * A settlement batch contains transactions captured together for settlement and funding.
 * Batches are read-only and cannot be created or updated via the API.
 *
 * All properties are read-only and only present in API responses.
 */
class Batch implements DataTransferObject
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
            'object' => ['credits', 'debits', 'net'],
            'enum' => ['state'],
            'string' => [
                'href', 'id', 'createdAt', 'modifiedAt', 'merchant',
                'processorAccount', 'terminal', 'account', 'processorReference',
            ],
        ];
    }

    public function __construct(
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $modifiedAt = null,
        public readonly ?string $merchant = null,
        public readonly ?string $processorAccount = null,
        public readonly ?string $terminal = null,
        public readonly ?string $account = null,
        public readonly ?string $processorReference = null,
        public readonly ?BatchState $state = null,
        public readonly ?CountAndTotal $credits = null,
        public readonly ?CountAndTotal $debits = null,
        public readonly ?CountAndTotal $net = null,
    ) {
    }
}
