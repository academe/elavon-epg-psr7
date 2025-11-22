<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\Transaction;

use Academe\Elavon\Epg\Psr7\Dtos\Transaction;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

/**
 * Transaction Response.
 *
 * Parses a PSR-7 response from the EPG API containing either transaction data or error details.
 *
 * For successful responses (2xx), contains transaction data.
 * For error responses (4xx, 5xx), contains error details.
 */
class TransactionResponse
{
    use HandlesErrors;

    private readonly ?Transaction $transaction;

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
            $this->transaction = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->transaction = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    /**
     * Creates a TransactionResponse from a PSR-7 response.
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
     * Gets the parsed Transaction object.
     *
     * Only available for successful responses (2xx status codes).
     *
     * @return Transaction|null Returns null if response was an error
     */
    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
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
     * Parses a successful response into a Transaction object.
     *
     * @return Transaction
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseSuccessResponse(): Transaction
    {
        $data = $this->parseJsonBody();
        return Transaction::fromData($data);
    }

}