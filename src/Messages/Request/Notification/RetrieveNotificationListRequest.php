<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Notification;

use Academe\Elavon\Epg\Psr7\Dtos\QueryParams;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;
use Psr\Http\Message\RequestInterface;

/**
 * Retrieve Notification List Request.
 *
 * Builds a PSR-7 request for retrieving paginated notification lists (GET /notifications).
 *
 * Supports filtering by createdAt_ge|gt|le|lt_timestamp and pagination via QueryParams.
 *
 * Note: This class builds the base request but does NOT add:
 * - Elavon API headers (Accept, Accept-Version)
 * - Environment configuration (sandbox, production, custom base URI)
 * - Authentication headers (Authorization)
 * Use the ElavonApiFactory to add these.
 */
class RetrieveNotificationListRequest
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
            $queryParams = QueryParams::fromArray($queryParams);
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
        $request = $this->getRequestFactory()->createRequest('GET', '/notifications');

        if (! $this->queryParams->isEmpty()) {
            $request = $request->withUri($this->queryParams->apply($request->getUri()));
        }

        return $request;
    }
}
