<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Terminal;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Retrieve Provisioning Code List Request.
 *
 * Builds a PSR-7 request for retrieving provisioning codes for a terminal (GET /terminals/{id}/provisioning-codes).
 *
 * This is a nested resource under terminals.
 *
 * Note: This class builds the base request but does NOT add:
 * - Elavon API headers (Accept, Accept-Version)
 * - Environment configuration (sandbox, production, custom base URI)
 * - Authentication headers (Authorization)
 * Use the ElavonApiRequest decorator to add these via fluent interface.
 */
class RetrieveProvisioningCodeListRequest
{
    /**
     * @param string $terminalId Terminal ID to retrieve provisioning codes for
     * @param array<string, mixed> $queryParams Query parameters for pagination/filtering
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     *
     * @throws InvalidArgumentException When terminal ID is empty
     */
    public function __construct(
        private readonly string $terminalId,
        private readonly array $queryParams = [],
        private readonly ?RequestFactoryInterface $requestFactory = null,
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
        $requestFactory = $this->requestFactory ?? new Psr17Factory();

        // Build URI with query parameters
        $uri = '/terminals/' . $this->terminalId . '/provisioning-codes';
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
