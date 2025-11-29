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

    /**
     * @param string|null $href Batch Resource URL (self link)
     * @param string|null $id Batch Resource ID assigned by server
     * @param string|null $createdAt Creation timestamp
     * @param string|null $modifiedAt Modification timestamp
     * @param string|null $merchant Merchant Resource URL
     * @param string|null $processorAccount ProcessorAccount Resource URL
     * @param string|null $terminal Terminal Resource URL
     * @param string|null $account Account Resource URL
     * @param string|null $processorReference Reference assigned by the processor
     * @param BatchState|string|null $state State of the batch
     * @param CountAndTotal|array<string, mixed>|null $credits Credits count and total
     * @param CountAndTotal|array<string, mixed>|null $debits Debits count and total
     * @param CountAndTotal|array<string, mixed>|null $net Net count and total (credits minus debits)
     */
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
