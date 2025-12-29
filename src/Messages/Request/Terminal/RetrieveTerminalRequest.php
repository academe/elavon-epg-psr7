<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Terminal;

use Academe\Elavon\Epg\Psr7\Contracts\RequestMessage;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Retrieve Terminal Request.
 *
 * Builds a PSR-7 request for retrieving a single terminal (GET /terminals/{id}).
 *
 * Note: This class builds the base request but does NOT add:
 * - Elavon API headers (Accept, Accept-Version)
 * - Environment configuration (sandbox, production, custom base URI)
 * - Authentication headers (Authorization)
 * Use the ElavonApiFactory to add these.
 */
class RetrieveTerminalRequest implements RequestMessage
{
    use HasPsr17Factories;

    /**
     * @param string $terminalId Terminal ID to retrieve     *
     * @throws InvalidArgumentException When terminal ID is empty
     */
    public function __construct(
        public readonly string $terminalId
    ) {
        if (empty($this->terminalId)) {
            throw new InvalidArgumentException('Terminal ID cannot be empty');
        }
    }

    /**
     * @param array{terminalId: string} $data
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('terminalId', $data)) {
            throw new InvalidArgumentException("Missing required key 'terminalId' in data");
        }

        return new static($data['terminalId']);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Build PSR-7 GET request
        return $this->getRequestFactory()
            ->createRequest('GET', '/terminals/' . $this->terminalId);
    }
}
