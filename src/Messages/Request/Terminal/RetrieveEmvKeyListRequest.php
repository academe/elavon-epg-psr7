<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Terminal;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Retrieve EMV Key List Request.
 *
 * Builds a PSR-7 request for retrieving EMV keys for a terminal (GET /terminals/{id}/emv-keys).
 *
 * This is a nested resource under terminals.
 *
 * Note: This class builds the base request but does NOT add:
 * - Elavon API headers (Accept, Accept-Version)
 * - Environment configuration (sandbox, production, custom base URI)
 * - Authentication headers (Authorization)
 * Use the ElavonApiFactory to add these.
 */
class RetrieveEmvKeyListRequest
{
    use HasPsr17Factories;

    /**
     * @param string $terminalId Terminal ID to retrieve EMV keys for
     * @param array<string, mixed> $queryParams Query parameters for pagination/filtering     *
     * @throws InvalidArgumentException When terminal ID is empty
     */
    public function __construct(
        private readonly string $terminalId,
        private readonly array $queryParams = []
    ) {
        if (empty($this->terminalId)) {
            throw new InvalidArgumentException('Terminal ID cannot be empty');
        }
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Use built-in factory if none provided
        $requestFactory = $this->getRequestFactory();

        // Build URI with query parameters
        $uri = '/terminals/' . $this->terminalId . '/emv-keys';
        if (!empty($this->queryParams)) {
            $uri .= '?' . http_build_query($this->queryParams);
        }

        // Build PSR-7 GET request
        return $requestFactory
            ->createRequest('GET', $uri);
    }

    /**
     * Gets the terminal ID.
     *
     * @return string
     */
    public function getTerminalId(): string
    {
        return $this->terminalId;
    }

    /**
     * Gets the query parameters.
     *
     * @return array<string, mixed>
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }
}
