<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\TotalAdjustment;

use Academe\Elavon\Epg\Psr7\Contracts\RequestMessage;
use Academe\Elavon\Epg\Psr7\Dtos\QueryParams;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\Concerns\HasPsr17Factories;
use Psr\Http\Message\RequestInterface;

class RetrieveTotalAdjustmentListRequest implements RequestMessage
{
    use HasPsr17Factories;

    /**
     * @param string $transactionId Transaction ID to retrieve total adjustments for
     * @param QueryParams $queryParams Query parameters for pagination/filtering
     * @throws InvalidArgumentException When transaction ID is empty
     */
    public function __construct(
        public readonly string $transactionId,
        public readonly QueryParams $queryParams = new QueryParams()
    ) {
        if (empty($this->transactionId)) {
            throw new InvalidArgumentException('Transaction ID cannot be empty');
        }
    }

    /**
     * Creates an instance from raw data.
     *
     * @param array{transactionId: string, queryParams?: QueryParams|array<string, mixed>} $data
     *
     * @throws InvalidArgumentException When required data is missing
     */
    public static function fromData(array $data): static
    {
        if (! array_key_exists('transactionId', $data)) {
            throw new InvalidArgumentException("Missing required key 'transactionId' in data");
        }

        $queryParams = $data['queryParams'] ?? new QueryParams();

        if (is_array($queryParams)) {
            $queryParams = QueryParams::fromArray($queryParams);
        }

        return new static($data['transactionId'], $queryParams);
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        $request = $this->getRequestFactory()
            ->createRequest('GET', '/transactions/' . $this->transactionId . '/total-adjustments');

        if (! $this->queryParams->isEmpty()) {
            $request = $request->withUri($this->queryParams->apply($request->getUri()));
        }

        return $request;
    }
}
