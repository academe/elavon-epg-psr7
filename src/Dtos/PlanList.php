<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;

/**
 * PlanList data transfer object.
 *
 * A plan list is associated with an account and contains pricing plans
 * for subscriptions and recurring payments.
 *
 * PlanLists are read-only via the API.
 */
class PlanList implements DataTransferObject
{
    use SerializesData;

    public function __construct(
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $modifiedAt = null,
        public readonly ?string $merchant = null,
        public readonly ?string $name = null,
        public readonly ?string $description = null,
    ) {
    }
}
