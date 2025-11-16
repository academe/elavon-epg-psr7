<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\DataObjects;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;

/**
 * Error Response.
 *
 * Represents an error response from the API.
 * Example: {"status":401,"failures":[{"code":"unauthorized","description":"...","field":null}]}
 */
class ErrorResponse
{
    /**
     * @param int $status HTTP status code from the error
     * @param ErrorDetail[] $failures Array of error details
     */
    public function __construct(
        public readonly int $status,
        public readonly array $failures = [],
    ) {
    }

    /**
     * Creates an ErrorResponse from an array.
     *
     * @param array<string, mixed> $data
     * @return self
     * @throws InvalidArgumentException
     */
    public static function fromArray(array $data): self
    {
        $failures = [];

        if (isset($data['failures']) && is_array($data['failures'])) {
            foreach ($data['failures'] as $failure) {
                if (is_array($failure)) {
                    $failures[] = ErrorDetail::fromArray($failure);
                }
            }
        }

        return new self(
            status: (int) ($data['status'] ?? 0),
            failures: $failures,
        );
    }

    /**
     * Converts the ErrorResponse to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'failures' => array_map(fn(ErrorDetail $detail) => $detail->toArray(), $this->failures),
        ];
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
