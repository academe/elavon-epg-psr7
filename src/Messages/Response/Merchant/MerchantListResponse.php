<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\Merchant;

use Academe\Elavon\Epg\Psr7\Dtos\Merchant;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

/**
 * Merchant List Response.
 *
 * Parses PSR-7 responses for paginated merchant lists (GET /merchants).
 */
class MerchantListResponse
{
    use HandlesErrors;

    private readonly ?array $merchants;
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
            $this->merchants = $data['items'];
            $this->nextPage = $data['next'];
            $this->firstPage = $data['first'];
            $this->error = null;
        } else {
            $this->merchants = null;
            $this->nextPage = null;
            $this->firstPage = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    /**
     * Creates a MerchantListResponse from a PSR-7 response.
     *
     * @param ResponseInterface $response PSR-7 HTTP response
     * @return static
     */
    public static function fromPsr7Response(ResponseInterface $response): static
    {
        return new static($response);
    }

    /**
     * Gets the merchants from a successful response.
     *
     * @return array<Merchant>|null Array of merchants on success, null on error
     */
    public function getMerchants(): ?array
    {
        return $this->merchants;
    }

    /**
     * Gets the URL for the next page of results.
     *
     * @return string|null URL if more pages exist, null otherwise
     */
    public function getNext(): ?string
    {
        return $this->nextPage;
    }

    /**
     * Gets the URL for the first page of results.
     *
     * @return string|null URL if available, null otherwise
     */
    public function getFirst(): ?string
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
     * Parses a successful response into a paginated list of merchants.
     *
     * @return array{items: array<Merchant>, next: string|null, first: string|null}
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseSuccessResponse(): array
    {
        $data = $this->parseJsonBody();

        // Validate structure
        if (!isset($data['items']) || !is_array($data['items'])) {
            throw new InvalidArgumentException('Response must contain an "items" array');
        }

        // Parse each merchant
        $merchants = [];
        foreach ($data['items'] as $index => $itemData) {
            if (!is_array($itemData)) {
                throw new InvalidArgumentException("Item at index {$index} is not an array");
            }

            $merchants[] = Merchant::fromData($itemData);
        }

        return [
            'items' => $merchants,
            'next' => isset($data['next']) ? (string) $data['next'] : null,
            'first' => isset($data['first']) ? (string) $data['first'] : null,
        ];
    }

}
