<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;

/**
 * Account data transfer object.
 *
 * A merchant may have multiple accounts, each containing configuration
 * and settings for payment processing.
 *
 * Accounts are read-only and cannot be created or updated via the API.
 * All properties are read-only and only present in API responses.
 */
class Account implements DataTransferObject
{
    use SerializesData;

    // Normalized properties (objects)
    // public readonly ?AutoSettleAt $autoSettleAt;

    /** @var array<ProcessorAccount>|null */
    public readonly ?array $processorAccounts;

    /**
     * Get property type definitions for this DTO.
     *
     * @return array<string, array<string>>
     */
    public static function getPropertyTypes(): array
    {
        return [
            'object' => ['autoSettleAt'],
            'array' => ['processorAccounts'],
            'string' => [
                'href', 'id', 'createdAt', 'modifiedAt', 'merchant',
                'name', 'description', 'tradeName', 'businessAddress',
                'businessPhone', 'businessEmail', 'businessWebsite',
                'planList', 'salesTaxEntry', 'signatureVerification',
                'logoUrl',
            ],
        ];
    }

    public function __construct(
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $modifiedAt = null,
        public readonly ?string $merchant = null,
        array|null $processorAccounts = null,
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly ?string $tradeName = null,
        public readonly ?string $businessAddress = null,
        public readonly ?string $businessPhone = null,
        public readonly ?string $businessEmail = null,
        public readonly ?string $businessWebsite = null,
        public readonly ?string $planList = null,
        public readonly ?string $salesTaxEntry = null,
        public readonly ?string $signatureVerification = null,
        public readonly ?string $logoUrl = null,
        public readonly ?AutoSettleAt $autoSettleAt = null,
    ) {
        // Normalize AutoSettleAt object
        // $this->autoSettleAt = match (true) {
        //     $autoSettleAt instanceof AutoSettleAt => $autoSettleAt,
        //     is_array($autoSettleAt) => AutoSettleAt::fromData($autoSettleAt),
        //     default => null,
        // };

        // Normalize ProcessorAccount array
        $this->processorAccounts = $this->normalizeProcessorAccounts($processorAccounts);
    }

    /**
     * Normalize an array of ProcessorAccount objects.
     *
     * @param array<ProcessorAccount|array<string, mixed>>|null $items
     * @return array<ProcessorAccount>|null
     */
    private function normalizeProcessorAccounts(?array $items): ?array
    {
        if ($items === null) {
            return null;
        }

        return array_map(
            fn ($item) => $item instanceof ProcessorAccount
                ? $item
                : ProcessorAccount::fromData($item),
            $items
        );
    }
}
