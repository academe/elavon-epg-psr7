<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\ManualBatch;

use Academe\Elavon\Epg\Psr7\Dtos\ManualBatch;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

/**
 * Manual Batch List Response.
 *
 * Parses PSR-7 responses for paginated manual batch lists (GET /manual-batches).
 *
 * Example usage:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Response\ManualBatch\ManualBatchListResponse;
 *
 * // Parse response from API
 * $response = ManualBatchListResponse::fromPsr7Response($psrResponse);
 *
 * if ($response->isSuccessful()) {
 *     foreach ($response->getManualBatches() as $manualBatch) {
 *         echo "Manual batch ID: " . $manualBatch->id . "\n";
 *     }
 *
 *     if ($response->hasMorePages()) {
 *         $nextPageUrl = $response->getNextPage();
 *         // Fetch next page...
 *     }
 * } else {
 *     $error = $response->getError();
 *     echo "Error: " . $error->message;
 * }
 * ```
 */
class ManualBatchListResponse
{
    use HandlesErrors;

    private readonly ?array $manualBatches;
    private readonly ?string $nextPage;
    private readonly ?string $firstPage;

    /**
     * @param ResponseInterface $response PSR-7 HTTP response
     * @throws InvalidArgumentException When response format is invalid
     */
    public function __construct(private readonly ResponseInterface $response)
    {
        // Parse response based on status code
        if ($this->isSuccessful()) {
            $data = $this->parseSuccessResponse();
            $this->manualBatches = $data['items'];
            $this->nextPage = $data['next'];
            $this->firstPage = $data['first'];
            $this->error = null;
        } else {
            $this->manualBatches = null;
            $this->nextPage = null;
            $this->firstPage = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    /**
     * Creates a ManualBatchListResponse from a PSR-7 response.
     *
     * @param ResponseInterface $response PSR-7 HTTP response
     * @return static
     */
    public static function fromPsr7Response(ResponseInterface $response): static
    {
        return new static($response);
    }

    /**
     * Gets the manual batches from a successful response.
     *
     * @return array<ManualBatch>|null Array of manual batches on success, null on error
     */
    public function getManualBatches(): ?array
    {
        return $this->manualBatches;
    }

    /**
     * Gets the URL for the next page of results.
     *
     * @return string|null URL if more pages exist, null otherwise
     */
    public function getNextPage(): ?string
    {
        return $this->nextPage;
    }

    /**
     * Gets the URL for the first page of results.
     *
     * @return string|null URL if available, null otherwise
     */
    public function getFirstPage(): ?string
    {
        return $this->firstPage;
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
     * Gets the PSR-7 response.
     *
     * @return ResponseInterface
     */
    public function getPsr7Response(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Gets the HTTP status code.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    /**
     * Parses a successful response into a paginated list of manual batches.
     *
     * @return array{items: array<ManualBatch>, next: string|null, first: string|null}
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseSuccessResponse(): array
    {
        $data = $this->parseJsonBody();

        // Validate structure
        if (!isset($data['items']) || !is_array($data['items'])) {
            throw new InvalidArgumentException('Response must contain an "items" array');
        }

        // Parse each manual batch
        $manualBatches = [];
        foreach ($data['items'] as $index => $itemData) {
            if (!is_array($itemData)) {
                throw new InvalidArgumentException("Item at index {$index} is not an array");
            }

            $manualBatches[] = ManualBatch::fromData($itemData);
        }

        return [
            'items' => $manualBatches,
            'next' => isset($data['next']) ? (string) $data['next'] : null,
            'first' => isset($data['first']) ? (string) $data['first'] : null,
        ];
    }

    /**
     * Parses the JSON response body.
     *
     * @return array<string, mixed>
     * @throws InvalidArgumentException When JSON is invalid
     */
    private function parseJsonBody(): array
    {
        $body = (string) $this->response->getBody();

        if ($body === '') {
            throw new InvalidArgumentException('Response body is empty');
        }

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new InvalidArgumentException(
                'Failed to decode JSON response: ' . $e->getMessage(),
                previous: $e
            );
        }

        if (!is_array($data)) {
            throw new InvalidArgumentException('Response body is not a JSON object');
        }

        // Check if it's an indexed array (JSON array) vs associative array (JSON object)
        if ($data === [] || array_keys($data) === range(0, count($data) - 1)) {
            throw new InvalidArgumentException('Response body is not a JSON object');
        }

        return $data;
    }
}
