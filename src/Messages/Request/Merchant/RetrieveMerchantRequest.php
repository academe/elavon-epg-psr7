<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Merchant;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Retrieve Merchant Request.
 *
 * Builds a PSR-7 request for retrieving a single merchant (GET /merchants/{id}).
 *
 * Note: This class builds the base request but does NOT add:
 * - Elavon API headers (Accept, Accept-Version)
 * - Environment configuration (sandbox, production, custom base URI)
 * - Authentication headers (Authorization)
 * Use the ElavonApiRequest decorator to add these via fluent interface.
 */
class RetrieveMerchantRequest
{
    /**
     * @param string $merchantId Merchant ID to retrieve
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     *
     * @throws InvalidArgumentException When merchant ID is empty
     */
    public function __construct(
        private readonly string $merchantId,
        private readonly ?RequestFactoryInterface $requestFactory = null,
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
        $requestFactory = $this->requestFactory ?? new Psr17Factory();

        // Build PSR-7 GET request
        return $requestFactory
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
