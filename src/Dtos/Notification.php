<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\EventType;
use Academe\Elavon\Epg\Psr7\Enums\ResourceType;

/**
 * Notification data transfer object.
 *
 * A notification provides details about potentially interesting events
 * happening within the Elavon Payment Gateway platform.
 *
 * Notifications are read-only and cannot be created or modified via the API.
 *
 * All properties are read-only and only present in API responses.
 */
class Notification implements DataTransferObject
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
                'href', 'id', 'merchant', 'createdAt', 'resource', 'customReference',
            ],
            'enum' => ['eventType', 'resourceType'],
        ];
    }

    /**
     * @param string|null $href Notification Resource URL (self link)
     * @param string|null $id Notification Resource ID assigned by server
     * @param string|null $merchant Merchant Resource URL
     * @param string|null $createdAt Creation timestamp
     * @param EventType|string|null $eventType The type of event that triggered this notification
     * @param ResourceType|string|null $resourceType The type of resource affected
     * @param string|null $resource Resource URL of the affected resource
     * @param string|null $customReference Optional reference provided by the merchant
     */
    public function __construct(
        public readonly ?string $href = null,
        public readonly ?string $id = null,
        public readonly ?string $merchant = null,
        public readonly ?string $createdAt = null,
        public readonly ?EventType $eventType = null,
        public readonly ?ResourceType $resourceType = null,
        public readonly ?string $resource = null,
        public readonly ?string $customReference = null,
    ) {
    }
}
