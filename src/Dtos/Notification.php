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
