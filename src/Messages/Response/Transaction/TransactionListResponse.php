<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\Transaction;

use Academe\Elavon\Epg\Psr7\Dtos\Transaction;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

/**
 * Transaction List Response.
 *
 * Parses a PSR-7 response from the EPG API containing either a paginated list
 * of transactions or error details.
 *
 * For successful responses (2xx), contains array of transactions with pagination links.
 * For error responses (4xx, 5xx), contains error details.
 */
class TransactionListResponse
{
    use ParsesPsr7Response;

    /** @var array<Transaction>|null */
    public readonly ?array $transactions;
    public readonly ?string $nextPage;
    public readonly ?string $firstPage;

    /**
     * @param array<string, mixed> $data Parsed response body data
     * @param int $statusCode HTTP status code
     *
     * @throws InvalidArgumentException When response cannot be parsed
     */
    public function __construct(array $data, int $statusCode) {
        $this->statusCode = $statusCode;

        // Parse response based on status code
        if ($this->isSuccessful()) {
            $parsed = $this->parseSuccessData($data);
            $this->transactions = $parsed['items'];
            $this->nextPage = $parsed['next'];
            $this->firstPage = $parsed['first'];
            $this->error = null;
        } else {
            $this->transactions = null;
            $this->nextPage = null;
            $this->firstPage = null;
            $this->error = self::parseErrorData($data);
        }
    }

    /**
     * Checks if there are more pages available.
     *
     * @return bool
     */
    public function hasMorePages(): bool
    {
        return $this->nextPage !== null;
    }
    /**
     * Parses a successful response into a paginated list of transactions.
     *
     * @return array{items: array<Transaction>, next: string|null, first: string|null}
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseSuccessData(array $data): array
    {
        // Validate structure
        if (!isset($data['items']) || !is_array($data['items'])) {
            throw new InvalidArgumentException('Response must contain an "items" array');
        }

        // Parse each transaction
        $transactions = [];
        foreach ($data['items'] as $index => $transactionData) {
            if (!is_array($transactionData)) {
                throw new InvalidArgumentException("Item at index {$index} is not an array");
            }

            $transactions[] = Transaction::fromData($transactionData);
        }
        return [
            'items' => $transactions,
            'next' => isset($data['next']) ? (string) $data['next'] : null,
            'first' => isset($data['first']) ? (string) $data['first'] : null,
        ];
    }

}
