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

    /**
     * Get property type definitions for this DTO.
     *
     * @return array<string, array<string>>
     */
    public static function getPropertyTypes(): array
    {
        return [
            'string' => [
                'href', 'id', 'createdAt', 'modifiedAt', 'merchant',
                'name', 'description',
            ],
        ];
    }

    /**
     * @param string|null $href PlanList Resource URL (self link)
     * @param string|null $id PlanList Resource ID assigned by server
     * @param string|null $createdAt Creation timestamp
     * @param string|null $modifiedAt Modification timestamp
     * @param string|null $merchant Merchant Resource URL
     * @param string|null $name Name
     * @param string|null $description Optional description
     */
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
