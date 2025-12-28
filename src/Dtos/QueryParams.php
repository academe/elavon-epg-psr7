<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Dtos;

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
 *     ->withFilter('createdAt', 'gt', '2024-01-01')
 *     ->withFilter('type', 'eq', 'refund');
 *
 * $uri = $params->apply($uri);
 * ```
 */
class QueryParams
{
    private const MIN_LIMIT = 1;
    private const MAX_LIMIT = 200;

    /**
     * Valid filter operators as defined in the Elavon API.
     */
    private const VALID_OPERATORS = [
        'eq',       // equals
        'ne',       // not equals
        'gt',       // greater than
        'ge',       // greater than or equal
        'lt',       // less than
        'le',       // less than or equal
        'like',     // like pattern
        'in',       // in list
        'contains', // contains
        'is',       // is (for null checks)
        'isnot',    // is not (for null checks)
    ];

    /**
     * @param string|null $pageToken Opaque continuation token for pagination
     * @param int|null $limit Maximum items per page (1-200)
     * @param array<int, array{field: string, operator: string, value: string}> $filters
     */
    public function __construct(
        public readonly ?string $pageToken = null,
        public readonly ?int $limit = null,
        public readonly array $filters = []
    ) {
        if ($limit !== null && ($limit < self::MIN_LIMIT || $limit > self::MAX_LIMIT)) {
            throw new InvalidArgumentException(
                sprintf('Limit must be between %d and %d, got %d', self::MIN_LIMIT, self::MAX_LIMIT, $limit)
            );
        }

        foreach ($filters as $filter) {
            if (! in_array($filter['operator'], self::VALID_OPERATORS, true)) {
                throw new InvalidArgumentException(
                    sprintf(
                        "Invalid filter operator '%s'. Valid operators: %s",
                        $filter['operator'],
                        implode(', ', self::VALID_OPERATORS)
                    )
                );
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
     * Create from an array of raw query parameters.
     *
     * Accepts arrays in the format used by http_build_query:
     * ['pageToken' => 'abc', 'limit' => 50, 'filter' => ['type_eq_refund', 'createdAt_gt_2024']]
     *
     * @param array<string, mixed> $params
     */
    public static function fromArray(array $params): static
    {
        $pageToken = isset($params['pageToken']) ? (string) $params['pageToken'] : null;
        $limit = isset($params['limit']) ? (int) $params['limit'] : null;

        $filters = [];
        if (isset($params['filter'])) {
            $filterStrings = is_array($params['filter']) ? $params['filter'] : [$params['filter']];
            foreach ($filterStrings as $filterString) {
                $parsed = self::parseFilterString((string) $filterString);
                if ($parsed !== null) {
                    $filters[] = $parsed;
                }
            }
        }

        return new static($pageToken, $limit, $filters);
    }

    /**
     * Parse a filter string like "createdAt_gt_2024-01-01" into components.
     *
     * @return array{field: string, operator: string, value: string}|null
     */
    private static function parseFilterString(string $filter): ?array
    {
        // Match pattern: field_operator_value
        // The operator is one of the known operators
        $operatorPattern = implode('|', self::VALID_OPERATORS);
        if (preg_match('/^(.+?)_(' . $operatorPattern . ')_(.+)$/', $filter, $matches)) {
            return [
                'field' => $matches[1],
                'operator' => $matches[2],
                'value' => $matches[3],
            ];
        }

        return null;
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
     * @param string $operator The operator (eq, ne, gt, ge, lt, le, like, in, contains, is, isnot)
     * @param string $value The value to filter by
     */
    public function withFilter(string $field, string $operator, string $value): static
    {
        $newFilters = $this->filters;
        $newFilters[] = [
            'field' => $field,
            'operator' => $operator,
            'value' => $value,
        ];

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
                $params['filter'][] = sprintf(
                    '%s_%s_%s',
                    $filter['field'],
                    $filter['operator'],
                    $filter['value']
                );
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
