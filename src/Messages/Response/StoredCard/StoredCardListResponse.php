<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\StoredCard;

use Academe\Elavon\Epg\Psr7\Dtos\StoredCard;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

/**
 * Stored Card List Response.
 *
 * Parses PSR-7 responses for paginated stored card lists (GET /stored-cards).
 *
 * Example usage:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Response\StoredCardListResponse;
 *
 * // Parse response from API
 * $response = StoredCardListResponse::fromPsr7Response($psrResponse);
 *
 * if ($response->isSuccessful()) {
 *     foreach ($response->getStoredCards() as $storedCard) {
 *         echo "Stored card ID: " . $storedCard->id . "\n";
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
class StoredCardListResponse
{
    use ParsesPsr7Response;

    /** @var array<StoredCard>|null */
    public readonly ?array $storedCards;
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
            $this->storedCards = $parsed['items'];
            $this->nextPage = $parsed['next'];
            $this->firstPage = $parsed['first'];
            $this->error = null;
        } else {
            $this->storedCards = null;
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
     * Parses a successful response into a paginated list of stored cards.
     *
     * @return array{items: array<StoredCard>, next: string|null, first: string|null}
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseSuccessData(array $data): array
    {
        // Validate structure
        if (!isset($data['items']) || !is_array($data['items'])) {
            throw new InvalidArgumentException('Response must contain an "items" array');
        }

        // Parse each stored card
        $storedCards = [];
        foreach ($data['items'] as $index => $itemData) {
            if (!is_array($itemData)) {
                throw new InvalidArgumentException("Item at index {$index} is not an array");
            }

            $storedCards[] = StoredCard::fromData($itemData);
        }
        return [
            'items' => $storedCards,
            'next' => isset($data['next']) ? (string) $data['next'] : null,
            'first' => isset($data['first']) ? (string) $data['first'] : null,
        ];
    }

}
