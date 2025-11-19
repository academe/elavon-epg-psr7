<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\Account;

use Academe\Elavon\Epg\Psr7\Dtos\Account;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

/**
 * Account Response.
 *
 * Parses a PSR-7 response from the EPG API containing either account data or error details.
 *
 * For successful responses (2xx), contains account data.
 * For error responses (4xx, 5xx), contains error details.
 */
class AccountResponse
{
    use HandlesErrors;

    private readonly ?Account $account;

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
            $this->account = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->account = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    /**
     * Creates an AccountResponse from a PSR-7 response.
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
     * Gets the parsed Account object.
     *
     * Only available for successful responses (2xx status codes).
     *
     * @return Account|null Returns null if response was an error
     */
    public function getAccount(): ?Account
    {
        return $this->account;
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
     * Parses a successful response into an Account object.
     *
     * @return Account
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseSuccessResponse(): Account
    {
        $data = $this->parseJsonBody();
        return Account::fromData($data);
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
        // JSON array [] or [1,2,3] should fail
        // JSON object {} or {"key":"value"} should pass
        if ($data === [] || array_keys($data) === range(0, count($data) - 1)) {
            throw new InvalidArgumentException('Response body is not a JSON object');
        }

        return $data;
    }
}
