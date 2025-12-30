<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

use Academe\Elavon\Epg\Psr7\Attributes\ArrayOf;
use Academe\Elavon\Epg\Psr7\Concerns\SerializesData;
use Academe\Elavon\Epg\Psr7\Contracts\DataTransferObject;
use Academe\Elavon\Epg\Psr7\Enums\QueryFilterOperator;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\UriInterface;

/**
 * Query parameters for list/collection endpoints.
 *
 * Supports pagination (pageToken, limit) and filtering.
 *
 * Usage:
 * ```php
 * $params = QueryParams::create()
 *     ->withLimit(50)
 *     ->withFilter('createdAt', QueryFilterOperator::GT, '2024-01-01')
 *     ->withFilter('type', QueryFilterOperator::EQ, 'refund');
 *
 * $uri = $params->apply($uri);
 * ```
 */
class QueryParams implements DataTransferObject
{
    use SerializesData;

    private const MIN_LIMIT = 1;
    private const MAX_LIMIT = 200;

    /**
     * @param string|null $pageToken Opaque continuation token for pagination
     * @param int|null $limit Maximum items per page (1-200)
     * @param array<QueryFilter>|null $filters
     */
    public function __construct(
        public readonly ?string $pageToken = null,
        public readonly ?int $limit = null,
        #[ArrayOf(QueryFilter::class)]
        public readonly ?array $filters = null
    ) {
        if ($limit !== null && ($limit < self::MIN_LIMIT || $limit > self::MAX_LIMIT)) {
            throw new InvalidArgumentException(
                sprintf('Limit must be between %d and %d, got %d', self::MIN_LIMIT, self::MAX_LIMIT, $limit)
            );
        }

        foreach ($filters ?? [] as $filter) {
            if (! $filter instanceof QueryFilter) {
                throw new InvalidArgumentException('Filters must be QueryFilter instances');
            }
        }
    }

    /**
     * Create a new empty QueryParams instance.
     */
    public static function create(): static
    {
        return new static();
    }

    /**
     * Set the pagination token.
     */
    public function withPageToken(?string $pageToken): static
    {
        return new static($pageToken, $this->limit, $this->filters);
    }

    /**
     * Set the page limit (1-200).
     */
    public function withLimit(?int $limit): static
    {
        return new static($this->pageToken, $limit, $this->filters);
    }

    /**
     * Add a filter condition.
     *
     * @param string $field The field to filter on (e.g., 'createdAt', 'type', 'total.amount')
     * @param QueryFilterOperator $operator The filter operator
     * @param string $value The value to filter by
     */
    public function withFilter(string $field, QueryFilterOperator $operator, string $value): static
    {
        $newFilters = $this->filters ?? [];
        $newFilters[] = new QueryFilter($field, $operator, $value);

        return new static($this->pageToken, $this->limit, $newFilters);
    }

    /**
     * Check if any parameters are set.
     */
    public function isEmpty(): bool
    {
        return $this->pageToken === null
            && $this->limit === null
            && empty($this->filters);
    }

    /**
     * Convert to a query string.
     */
    public function toQueryString(): string
    {
        $params = $this->toArray();

        if (empty($params)) {
            return '';
        }

        // Build query string, handling multiple filter values
        $parts = [];

        if (isset($params['pageToken'])) {
            $parts[] = 'pageToken=' . urlencode($params['pageToken']);
        }

        if (isset($params['limit'])) {
            $parts[] = 'limit=' . $params['limit'];
        }

        if (isset($params['filter'])) {
            foreach ($params['filter'] as $filter) {
                $parts[] = 'filter=' . urlencode($filter);
            }
        }

        return implode('&', $parts);
    }

    /**
     * Convert to an array suitable for http_build_query.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $params = [];

        if ($this->pageToken !== null) {
            $params['pageToken'] = $this->pageToken;
        }

        if ($this->limit !== null) {
            $params['limit'] = $this->limit;
        }

        if (! empty($this->filters)) {
            $params['filter'] = [];
            foreach ($this->filters as $filter) {
                $params['filter'][] = $filter->toFilterString();
            }
        }

        return $params;
    }

    /**
     * Apply query parameters to a URI.
     *
     * @param UriInterface $uri The URI to modify
     * @return UriInterface The modified URI with query parameters applied
     */
    public function apply(UriInterface $uri): UriInterface
    {
        if ($this->isEmpty()) {
            return $uri;
        }

        $queryString = $this->toQueryString();

        // Merge with any existing query string
        $existingQuery = $uri->getQuery();
        if ($existingQuery !== '') {
            $queryString = $existingQuery . '&' . $queryString;
        }

        return $uri->withQuery($queryString);
    }
}
