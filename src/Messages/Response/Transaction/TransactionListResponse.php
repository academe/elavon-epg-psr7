<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\Transaction;

use Academe\Elavon\Epg\Psr7\Dtos\Transaction;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

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
    use HandlesErrors;

    /** @var array<Transaction>|null */
    private readonly ?array $transactions;
    private readonly ?string $nextPage;
    private readonly ?string $firstPage;

    /**
     * @param ResponseInterface $response PSR-7 response from the API
     *
     * @throws InvalidArgumentException When response cannot be parsed
     */
    public function __construct(
        private readonly ResponseInterface $response,
    ) {
        // Parse response based on status code
        if ($this->isSuccessful()) {
            $data = $this->parseSuccessResponse();
            $this->transactions = $data['items'];
            $this->nextPage = $data['next'];
            $this->firstPage = $data['first'];
            $this->error = null;
        } else {
            $this->transactions = null;
            $this->nextPage = null;
            $this->firstPage = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    /**
     * Creates a TransactionListResponse from a PSR-7 response.
     *
     * @param ResponseInterface $response PSR-7 response
     *
     * @return self
     * @throws InvalidArgumentException When response cannot be parsed
     */
    public static function fromPsr7Response(ResponseInterface $response): self
    {
        return new self($response);
    }

    /**
     * Gets the list of transactions.
     *
     * Only available for successful responses (2xx status codes).
     *
     * @return array<Transaction>|null Returns null if response was an error
     */
    public function getTransactions(): ?array
    {
        return $this->transactions;
    }

    /**
     * Gets the URL for the next page of results.
     *
     * Returns null if there are no more pages.
     *
     * @return string|null
     */
    public function getNextPage(): ?string
    {
        return $this->nextPage;
    }

    /**
     * Gets the URL for the first page of results.
     *
     * @return string|null
     */
    public function getFirstPage(): ?string
    {
        return $this->firstPage;
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
     * Gets the HTTP status code.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    /**
     * Gets the original PSR-7 response.
     *
     * @return ResponseInterface
     */
    public function getPsr7Response(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Parses a successful response into a paginated list of transactions.
     *
     * @return array{items: array<Transaction>, next: string|null, first: string|null}
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseSuccessResponse(): array
    {
        $data = $this->parseJsonBody();

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
