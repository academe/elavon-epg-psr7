<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\Subscription;

use Academe\Elavon\Epg\Psr7\Dtos\Subscription;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

/**
 * Subscription List Response.
 *
 * Parses PSR-7 responses for paginated subscription lists (GET /subscriptions).
 *
 * Example usage:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Response\Subscription\SubscriptionListResponse;
 *
 * // Parse response from API
 * $response = SubscriptionListResponse::fromPsr7Response($psrResponse);
 *
 * if ($response->isSuccessful()) {
 *     foreach ($response->getSubscriptions() as $subscription) {
 *         echo "Subscription ID: " . $subscription->id . "\n";
 *     }
 *
 *     if ($response->hasMorePages()) {
 *         $nextPageUrl = $response->getNext();
 *         // Fetch next page...
 *     }
 * } else {
 *     $error = $response->getError();
 *     echo "Error: " . $error->message;
 * }
 * ```
 */
class SubscriptionListResponse
{
    use ParsesPsr7Response;

    /** @var array<Subscription>|null */
    public readonly ?array $subscriptions;
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
            $this->subscriptions = $parsed['items'];
            $this->nextPage = $parsed['next'];
            $this->firstPage = $parsed['first'];
            $this->error = null;
        } else {
            $this->subscriptions = null;
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
     * Parses a successful response into a paginated list of subscriptions.
     *
     * @return array{items: array<Subscription>, next: string|null, first: string|null}
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseSuccessData(array $data): array
    {
        // Validate structure
        if (!isset($data['items']) || !is_array($data['items'])) {
            throw new InvalidArgumentException('Response must contain an "items" array');
        }

        // Parse each subscription
        $subscriptions = [];
        foreach ($data['items'] as $index => $itemData) {
            if (!is_array($itemData)) {
                throw new InvalidArgumentException("Item at index {$index} is not an array");
            }

            $subscriptions[] = Subscription::fromData($itemData);
        }
        return [
            'items' => $subscriptions,
            'next' => isset($data['next']) ? (string) $data['next'] : null,
            'first' => isset($data['first']) ? (string) $data['first'] : null,
        ];
    }

}
