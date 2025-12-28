<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Shopper;

use Academe\Elavon\Epg\Psr7\Dtos\QueryParams;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;
use Psr\Http\Message\RequestInterface;

class RetrieveShopperStoredCardsRequest
{
    use HasPsr17Factories;

    /**
     * @param string $shopperId Shopper ID to retrieve stored cards for
     * @param QueryParams $queryParams Query parameters for pagination/filtering
     * @throws InvalidArgumentException When shopper ID is empty
     */
    public function __construct(
        public readonly string $shopperId,
        public readonly QueryParams $queryParams = new QueryParams()
    ) {
        if (empty($this->shopperId)) {
            throw new InvalidArgumentException('Shopper ID cannot be empty');
        }
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{shopperId: string, queryParams?: QueryParams|array<string, mixed>} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('shopperId', $data)) {
            throw new InvalidArgumentException("Missing required key 'shopperId' in data");
        }

        $queryParams = $data['queryParams'] ?? new QueryParams();

        if (is_array($queryParams)) {
            $queryParams = QueryParams::fromArray($queryParams);
        }

        return new static($data['shopperId'], $queryParams);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        $request = $this->getRequestFactory()
            ->createRequest('GET', '/shoppers/' . $this->shopperId . '/stored-cards');

        if (! $this->queryParams->isEmpty()) {
            $request = $request->withUri($this->queryParams->apply($request->getUri()));
        }

        return $request;
    }
}
