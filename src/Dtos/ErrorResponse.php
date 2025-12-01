<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Attributes\ArrayOf;
use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;

/**
 * Error Response.
 *
 * Represents an error response from the API.
 * Example: {"status":401,"failures":[{"code":"unauthorized","description":"...","field":null}]}
 */
class ErrorResponse implements DataTransferObject
{
    use SerializesData;

    /** @var ErrorDetail[] */
    public readonly array $failures;

    public function __construct(
        public readonly ?int $status = null,
        /** @var ErrorDetail[]|null */
        #[ArrayOf(ErrorDetail::class)]
        ?array $failures = null,
    ) {
        $this->failures = $failures ?? [];
    }

    /**
     * Gets the primary error message (from first failure).
     *
     * @return string
     */
    public function getMessage(): string
    {
        if (empty($this->failures)) {
            return "HTTP {$this->status} error";
        }

        return $this->failures[0]->description;
    }

    /**
     * Gets the primary error code (from first failure).
     *
     * @return string
     */
    public function getCode(): string
    {
        if (empty($this->failures)) {
            return 'unknown';
        }

        return $this->failures[0]->code;
    }

    /**
     * Gets all failure details.
     *
     * @return ErrorDetail[]
     */
    public function getFailures(): array
    {
        return $this->failures;
    }

    /**
     * Checks if this is a specific error code.
     *
     * @param string $code
     * @return bool
     */
    public function hasErrorCode(string $code): bool
    {
        foreach ($this->failures as $failure) {
            if ($failure->code === $code) {
                return true;
            }
        }

        return false;
    }
}
