<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PaymentMethodLink;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;

/**
 * Retrieve PaymentMethodLink List Request.
 *
 * Builds a PSR-7 request for retrieving paginated payment method link lists (GET /payment-method-links).
 *
 * Supports pagination via query parameters (page, limit, etc.).
 */
class RetrievePaymentMethodLinkListRequest
{
    use HasPsr17Factories;

    /**
     * @param array<string, mixed> $queryParams Query parameters for pagination/filtering     */
    public function __construct(
        private readonly array $queryParams = []
    ) {
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Use built-in factory if none provided

        // Build URI with query parameters
        $uri = '/payment-method-links';
        if (!empty($this->queryParams)) {
            $uri .= '?' . http_build_query($this->queryParams);
        }

        // Build PSR-7 GET request
        return $this->getRequestFactory()
            ->createRequest('GET', $uri);
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
