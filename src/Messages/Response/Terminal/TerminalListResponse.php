<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\Terminal;

use Academe\Elavon\Epg\Psr7\Dtos\Terminal;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

/**
 * Terminal List Response.
 *
 * Parses PSR-7 responses for paginated terminal lists (GET /terminals).
 */
class TerminalListResponse
{
    use ParsesPsr7Response;

    /** @var array<Terminal>|null */
    public readonly ?array $terminals;
    public readonly ?string $nextPage;
    public readonly ?string $firstPage;

    /**
     * @param array<string, mixed> $data Parsed response body data
     * @param int $statusCode HTTP status code
     * @throws InvalidArgumentException When response format is invalid
     */
    public function __construct(array $data, int $statusCode)
    {
        $this->statusCode = $statusCode;

        // Parse response based on status code
        if ($this->isSuccessful()) {
            $parsed = $this->parseSuccessData($data);
            $this->terminals = $parsed['items'];
            $this->nextPage = $parsed['next'];
            $this->firstPage = $parsed['first'];
            $this->error = null;
        } else {
            $this->terminals = null;
            $this->nextPage = null;
            $this->firstPage = null;
            $this->error = self::parseErrorData($data);
        }
    }

    /**
     * Checks if there are more pages of results.
     *
     * @return bool True if more pages exist, false otherwise
     */
    public function hasMorePages(): bool
    {
        return $this->nextPage !== null;
    }
    /**
     * Parses a successful response into a paginated list of terminals.
     *
     * @return array{items: array<Terminal>, next: string|null, first: string|null}
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseSuccessData(array $data): array
    {
        // Validate structure
        if (!isset($data['items']) || !is_array($data['items'])) {
            throw new InvalidArgumentException('Response must contain an "items" array');
        }

        // Parse each terminal
        $terminals = [];
        foreach ($data['items'] as $index => $itemData) {
            if (!is_array($itemData)) {
                throw new InvalidArgumentException("Item at index {$index} is not an array");
            }

            $terminals[] = Terminal::fromData($itemData);
        }
        return [
            'items' => $terminals,
            'next' => isset($data['next']) ? (string) $data['next'] : null,
            'first' => isset($data['first']) ? (string) $data['first'] : null,
        ];
    }

}
