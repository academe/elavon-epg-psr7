<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Merchant;

use Academe\Elavon\Epg\Psr7\Contracts\RequestMessage;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Retrieve Merchant Request.
 *
 * Builds a PSR-7 request for retrieving a single merchant (GET /merchants/{id}).
 *
 * Note: This class builds the base request but does NOT add:
 * - Elavon API headers (Accept, Accept-Version)
 * - Environment configuration (sandbox, production, custom base URI)
 * - Authentication headers (Authorization)
 * Use the ElavonApiFactory to add these.
 */
class RetrieveMerchantRequest implements RequestMessage
{
    use HasPsr17Factories;

    /**
     * @param string $merchantId Merchant ID to retrieve     *
     * @throws InvalidArgumentException When merchant ID is empty
     */
    public function __construct(
        public readonly string $merchantId
    ) {
        if (empty($this->merchantId)) {
            throw new InvalidArgumentException('Merchant ID cannot be empty');
        }
    }

    /**
     * @param array{merchantId: string} $data
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('merchantId', $data)) {
            throw new InvalidArgumentException("Missing required key 'merchantId' in data");
        }

        return new static($data['merchantId']);
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
            ->createRequest('GET', '/merchants/' . $this->merchantId);
    }
}
