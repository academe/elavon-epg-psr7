<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Attributes\ArrayOf;
use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use DateTimeImmutable;

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

    public function __construct(
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $modifiedAt = null,
        public readonly ?string $merchant = null,
        /** @var array<ProcessorAccount>|null */
        #[ArrayOf(ProcessorAccount::class)]
        public readonly ?array $processorAccounts = null,
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
    }
}
