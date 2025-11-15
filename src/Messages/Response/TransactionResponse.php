<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response;

use Academe\Elavon\Epg\Psr7\DataObjects\Transaction;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

/**
 * Transaction Response.
 *
 * Parses a PSR-7 response from the EPG API containing transaction data.
 */
class TransactionResponse
{
    private readonly Transaction $transaction;

    /**
     * @param ResponseInterface $response PSR-7 response from the API
     *
     * @throws InvalidArgumentException When response cannot be parsed
     */
    public function __construct(
        private readonly ResponseInterface $response,
    ) {
        $this->transaction = $this->parseResponse();
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
     * @return Transaction
     */
    public function getTransaction(): Transaction
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
     * Checks if the response was successful (2xx status code).
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        $statusCode = $this->getStatusCode();
        return $statusCode >= 200 && $statusCode < 300;
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
     * Parses the PSR-7 response into a Transaction object.
     *
     * @return Transaction
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseResponse(): Transaction
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

        return Transaction::fromArray($data);
    }
}