<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\PaymentMethodLink;

use Academe\Elavon\Epg\Psr7\Contracts\RequestMessage;
use Academe\Elavon\Epg\Psr7\Dtos\QueryParams;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;
use Psr\Http\Message\RequestInterface;

/**
 * Retrieve PaymentMethodLink List Request.
 *
 * Builds a PSR-7 request for retrieving paginated payment method link lists (GET /payment-method-links).
 *
 * Supports pagination via QueryParams (pageToken, limit).
 */
class RetrievePaymentMethodLinkListRequest implements RequestMessage
{
    use HasPsr17Factories;

    public function __construct(
        public readonly QueryParams $queryParams = new QueryParams()
    ) {
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{queryParams?: QueryParams|array<string, mixed>} $data
     */
    public static function fromData(array $data): static
    {
        $queryParams = $data['queryParams'] ?? new QueryParams();

        if (is_array($queryParams)) {
            $queryParams = QueryParams::fromData($queryParams);
        }

        return new static($queryParams);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        $request = $this->getRequestFactory()->createRequest('GET', '/payment-method-links');

        if (! $this->queryParams->isEmpty()) {
            $request = $request->withUri($this->queryParams->apply($request->getUri()));
        }

        return $request;
    }
}
