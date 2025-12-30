<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\DoAutoCaptureAfterTimeUnit;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * Do Auto Capture After data transfer object.
 *
 * Configuration for automatic capture after a specified time period.
 */
class DoAutoCaptureAfter implements DataTransferObject
{
    use SerializesData;

    public function __construct(
        public readonly ?DoAutoCaptureAfterTimeUnit $timeUnit = null,
        public readonly ?int $count = null,
    ) {
        $this->validate();
    }

    /**
     * Validates auto capture after data.
     *
     * @throws InvalidArgumentException When validation fails
     */
    private function validate(): void
    {
        // Count must be at least 1 if provided
        if ($this->count !== null && $this->count < 1) {
            throw new InvalidArgumentException('Count must be at least 1');
        }
    }
}