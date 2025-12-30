<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * Point of Interaction data transfer object.
 *
 * Information about the location of interaction.
 */
class PointOfInteraction implements DataTransferObject
{
    use SerializesData;

    public function __construct(
        public readonly ?string $laneNumber = null,
    ) {
        $this->validate();
    }

    /**
     * Validates point of interaction data.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // Lane number must be 0-8 digits
        if ($this->laneNumber !== null && !preg_match('/^\d{0,8}$/', $this->laneNumber)) {
            throw new InvalidArgumentException('Lane number must be 0-8 digits');
        }
    }
}