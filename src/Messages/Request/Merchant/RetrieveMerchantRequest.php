<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Merchant;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
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
class RetrieveMerchantRequest
{
    use HasPsr17Factories;

    /**
     * @param string $merchantId Merchant ID to retrieve     *
     * @throws InvalidArgumentException When merchant ID is empty
     */
    public function __construct(
        private readonly string $merchantId
    ) {
        if (empty($this->merchantId)) {
            throw new InvalidArgumentException('Merchant ID cannot be empty');
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

        // Build PSR-7 GET request
        return $this->getRequestFactory()
            ->createRequest('GET', '/merchants/' . $this->merchantId);
    }

    /**
     * Gets the merchant ID being retrieved.
     *
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }
}
