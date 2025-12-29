<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\ProcessorAccount;

use Academe\Elavon\Epg\Psr7\Contracts\RequestMessage;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Retrieve ProcessorAccount Request.
 *
 * Builds a PSR-7 request for retrieving a single processor account (GET /processor-accounts/{id}).
 *
 * Note: This class builds the base request but does NOT add:
 * - Elavon API headers (Accept, Accept-Version)
 * - Environment configuration (sandbox, production, custom base URI)
 * - Authentication headers (Authorization)
 * Use the ElavonApiFactory to add these.
 */
class RetrieveProcessorAccountRequest implements RequestMessage
{
    use HasPsr17Factories;

    /**
     * @param string $processorAccountId ProcessorAccount ID to retrieve     *
     * @throws InvalidArgumentException When processor account ID is empty
     */
    public function __construct(
        public readonly string $processorAccountId
    ) {
        if (empty($this->processorAccountId)) {
            throw new InvalidArgumentException('ProcessorAccount ID cannot be empty');
        }
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{processorAccountId: string} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('processorAccountId', $data)) {
            throw new InvalidArgumentException("Missing required key 'processorAccountId' in data");
        }

        return new static($data['processorAccountId']);
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
            ->createRequest('GET', '/processor-accounts/' . $this->processorAccountId);
    }
}
