<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Terminal;

use Academe\Elavon\Epg\Psr7\Contracts\RequestMessage;
use Academe\Elavon\Epg\Psr7\Dtos\QueryParams;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;
use Psr\Http\Message\RequestInterface;

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
class RetrieveEmvKeyListRequest implements RequestMessage
{
    use HasPsr17Factories;

    /**
     * @param string $terminalId Terminal ID to retrieve EMV keys for
     * @param QueryParams $queryParams Query parameters for pagination
     * @throws InvalidArgumentException When terminal ID is empty
     */
    public function __construct(
        public readonly string $terminalId,
        public readonly QueryParams $queryParams = new QueryParams()
    ) {
        if (empty($this->terminalId)) {
            throw new InvalidArgumentException('Terminal ID cannot be empty');
        }
    }

    /**
     * @param array{terminalId: string, queryParams?: QueryParams|array<string, mixed>} $data
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('terminalId', $data)) {
            throw new InvalidArgumentException("Missing required key 'terminalId' in data");
        }

        $queryParams = $data['queryParams'] ?? new QueryParams();

        if (is_array($queryParams)) {
            $queryParams = QueryParams::fromArray($queryParams);
        }

        return new static($data['terminalId'], $queryParams);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        $request = $this->getRequestFactory()
            ->createRequest('GET', '/terminals/' . $this->terminalId . '/emv-keys');

        if (! $this->queryParams->isEmpty()) {
            $request = $request->withUri($this->queryParams->apply($request->getUri()));
        }

        return $request;
    }
}
