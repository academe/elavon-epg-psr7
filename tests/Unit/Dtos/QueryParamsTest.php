<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\QueryFilter;
use Academe\Elavon\Epg\Psr7\Dtos\QueryParams;
use Academe\Elavon\Epg\Psr7\Enums\QueryFilterOperator;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;

/**
 * Tests for QueryParams value object.
 */
class QueryParamsTest extends TestCase
{
    public function test_create_returnsEmptyInstance(): void
    {
        $params = QueryParams::create();

        $this->assertNull($params->pageToken);
        $this->assertNull($params->limit);
        $this->assertEmpty($params->filters);
        $this->assertTrue($params->isEmpty());
    }

    public function test_construct_withPageToken_setsPageToken(): void
    {
        $params = new QueryParams(pageToken: 'abc123');

        $this->assertSame('abc123', $params->pageToken);
    }

    public function test_construct_withValidLimit_setsLimit(): void
    {
        $params = new QueryParams(limit: 50);

        $this->assertSame(50, $params->limit);
    }

    public function test_construct_withMinLimit_setsLimit(): void
    {
        $params = new QueryParams(limit: 1);

        $this->assertSame(1, $params->limit);
    }

    public function test_construct_withMaxLimit_setsLimit(): void
    {
        $params = new QueryParams(limit: 200);

        $this->assertSame(200, $params->limit);
    }

    public function test_construct_withLimitBelowMin_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Limit must be between 1 and 200, got 0');

        new QueryParams(limit: 0);
    }

    public function test_construct_withLimitAboveMax_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Limit must be between 1 and 200, got 201');

        new QueryParams(limit: 201);
    }

    public function test_construct_withInvalidFilter_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Filters must be QueryFilter instances');

        new QueryParams(filters: [
            ['field' => 'type', 'operator' => 'eq', 'value' => 'refund'],
        ]);
    }

    public function test_construct_withQueryFilterInstances_succeeds(): void
    {
        $filter = new QueryFilter('type', QueryFilterOperator::EQ, 'refund');
        $params = new QueryParams(filters: [$filter]);

        $this->assertCount(1, $params->filters);
        $this->assertSame('type', $params->filters[0]->field);
        $this->assertSame(QueryFilterOperator::EQ, $params->filters[0]->operator);
        $this->assertSame('refund', $params->filters[0]->value);
    }

    public function test_withPageToken_returnsNewInstance(): void
    {
        $params = QueryParams::create();
        $newParams = $params->withPageToken('token123');

        $this->assertNull($params->pageToken);
        $this->assertSame('token123', $newParams->pageToken);
    }

    public function test_withLimit_returnsNewInstance(): void
    {
        $params = QueryParams::create();
        $newParams = $params->withLimit(25);

        $this->assertNull($params->limit);
        $this->assertSame(25, $newParams->limit);
    }

    public function test_withFilter_addsFilter(): void
    {
        $params = QueryParams::create()
            ->withFilter('type', QueryFilterOperator::EQ, 'refund');

        $this->assertCount(1, $params->filters);
        $this->assertSame('type', $params->filters[0]->field);
        $this->assertSame(QueryFilterOperator::EQ, $params->filters[0]->operator);
        $this->assertSame('refund', $params->filters[0]->value);
    }

    public function test_withFilter_addsMultipleFilters(): void
    {
        $params = QueryParams::create()
            ->withFilter('type', QueryFilterOperator::EQ, 'refund')
            ->withFilter('createdAt', QueryFilterOperator::GT, '2024-01-01');

        $this->assertCount(2, $params->filters);
        $this->assertSame('type', $params->filters[0]->field);
        $this->assertSame('createdAt', $params->filters[1]->field);
    }

    /**
     * @dataProvider validOperatorsProvider
     */
    public function test_withFilter_acceptsValidOperators(QueryFilterOperator $operator): void
    {
        $params = QueryParams::create()->withFilter('field', $operator, 'value');

        $this->assertSame($operator, $params->filters[0]->operator);
    }

    public static function validOperatorsProvider(): array
    {
        return [
            'eq' => [QueryFilterOperator::EQ],
            'ne' => [QueryFilterOperator::NE],
            'gt' => [QueryFilterOperator::GT],
            'ge' => [QueryFilterOperator::GE],
            'lt' => [QueryFilterOperator::LT],
            'le' => [QueryFilterOperator::LE],
            'like' => [QueryFilterOperator::LIKE],
            'in' => [QueryFilterOperator::IN],
            'contains' => [QueryFilterOperator::CONTAINS],
            'is' => [QueryFilterOperator::IS],
            'isnot' => [QueryFilterOperator::IS_NOT],
        ];
    }

    public function test_isEmpty_returnsTrueForEmptyParams(): void
    {
        $params = QueryParams::create();

        $this->assertTrue($params->isEmpty());
    }

    public function test_isEmpty_returnsFalseWithPageToken(): void
    {
        $params = QueryParams::create()->withPageToken('token');

        $this->assertFalse($params->isEmpty());
    }

    public function test_isEmpty_returnsFalseWithLimit(): void
    {
        $params = QueryParams::create()->withLimit(10);

        $this->assertFalse($params->isEmpty());
    }

    public function test_isEmpty_returnsFalseWithFilter(): void
    {
        $params = QueryParams::create()->withFilter('type', QueryFilterOperator::EQ, 'sale');

        $this->assertFalse($params->isEmpty());
    }

    public function test_toQueryString_withEmptyParams_returnsEmptyString(): void
    {
        $params = QueryParams::create();

        $this->assertSame('', $params->toQueryString());
    }

    public function test_toQueryString_withPageToken_returnsCorrectString(): void
    {
        $params = QueryParams::create()->withPageToken('abc123');

        $this->assertSame('pageToken=abc123', $params->toQueryString());
    }

    public function test_toQueryString_withLimit_returnsCorrectString(): void
    {
        $params = QueryParams::create()->withLimit(50);

        $this->assertSame('limit=50', $params->toQueryString());
    }

    public function test_toQueryString_withFilter_returnsCorrectString(): void
    {
        $params = QueryParams::create()->withFilter('type', QueryFilterOperator::EQ, 'refund');

        $this->assertSame('filter=type_eq_refund', $params->toQueryString());
    }

    public function test_toQueryString_withMultipleFilters_returnsCorrectString(): void
    {
        $params = QueryParams::create()
            ->withFilter('type', QueryFilterOperator::EQ, 'refund')
            ->withFilter('createdAt', QueryFilterOperator::GT, '2024-01-01');

        $this->assertSame(
            'filter=type_eq_refund&filter=createdAt_gt_2024-01-01',
            $params->toQueryString()
        );
    }

    public function test_toQueryString_withAllParams_returnsCorrectString(): void
    {
        $params = QueryParams::create()
            ->withPageToken('token123')
            ->withLimit(25)
            ->withFilter('type', QueryFilterOperator::EQ, 'sale');

        $this->assertSame(
            'pageToken=token123&limit=25&filter=type_eq_sale',
            $params->toQueryString()
        );
    }

    public function test_toQueryString_encodesSpecialCharacters(): void
    {
        $params = QueryParams::create()->withPageToken('token with spaces');

        $this->assertSame('pageToken=token+with+spaces', $params->toQueryString());
    }

    public function test_toArray_withEmptyParams_returnsEmptyArray(): void
    {
        $params = QueryParams::create();

        $this->assertSame([], $params->toArray());
    }

    public function test_toArray_withAllParams_returnsArray(): void
    {
        $params = QueryParams::create()
            ->withPageToken('token123')
            ->withLimit(25)
            ->withFilter('type', QueryFilterOperator::EQ, 'sale')
            ->withFilter('createdAt', QueryFilterOperator::GT, '2024');

        $array = $params->toArray();

        $this->assertSame('token123', $array['pageToken']);
        $this->assertSame(25, $array['limit']);
        $this->assertCount(2, $array['filter']);
        $this->assertSame('type_eq_sale', $array['filter'][0]);
        $this->assertSame('createdAt_gt_2024', $array['filter'][1]);
    }

    public function test_apply_withEmptyParams_returnsUnchangedUri(): void
    {
        $params = QueryParams::create();
        $uri = new Uri('/transactions');

        $result = $params->apply($uri);

        $this->assertSame('/transactions', (string) $result);
    }

    public function test_apply_withParams_addsQueryString(): void
    {
        $params = QueryParams::create()->withLimit(50);
        $uri = new Uri('/transactions');

        $result = $params->apply($uri);

        $this->assertSame('/transactions?limit=50', (string) $result);
    }

    public function test_apply_withExistingQuery_mergesParams(): void
    {
        $params = QueryParams::create()->withLimit(50);
        $uri = new Uri('/transactions?existing=value');

        $result = $params->apply($uri);

        $this->assertSame('/transactions?existing=value&limit=50', (string) $result);
    }

    public function test_apply_withMultipleFilters_addsAllFilters(): void
    {
        $params = QueryParams::create()
            ->withFilter('type', QueryFilterOperator::EQ, 'refund')
            ->withFilter('createdAt', QueryFilterOperator::GT, '2024-01-01');
        $uri = new Uri('/transactions');

        $result = $params->apply($uri);

        $this->assertStringContainsString('filter=type_eq_refund', (string) $result);
        $this->assertStringContainsString('filter=createdAt_gt_2024-01-01', (string) $result);
    }

    public function test_apply_withFullUri_preservesOtherComponents(): void
    {
        $params = QueryParams::create()->withLimit(10);
        $uri = new Uri('https://api.example.com/transactions');

        $result = $params->apply($uri);

        $this->assertSame('https://api.example.com/transactions?limit=10', (string) $result);
    }

    public function test_chainedMethods_preserveAllValues(): void
    {
        $params = QueryParams::create()
            ->withPageToken('page2')
            ->withLimit(100)
            ->withFilter('type', QueryFilterOperator::EQ, 'sale')
            ->withFilter('state', QueryFilterOperator::IN, 'authorized,captured');

        $this->assertSame('page2', $params->pageToken);
        $this->assertSame(100, $params->limit);
        $this->assertCount(2, $params->filters);
    }

    public function test_immutability_originalNotModified(): void
    {
        $original = QueryParams::create();
        $modified = $original
            ->withPageToken('token')
            ->withLimit(50)
            ->withFilter('type', QueryFilterOperator::EQ, 'sale');

        $this->assertTrue($original->isEmpty());
        $this->assertFalse($modified->isEmpty());
    }

    public function test_properties_areReadonly(): void
    {
        $params = new QueryParams(pageToken: 'token', limit: 50);

        $reflection = new \ReflectionProperty($params, 'pageToken');
        $this->assertTrue($reflection->isReadOnly());

        $reflection = new \ReflectionProperty($params, 'limit');
        $this->assertTrue($reflection->isReadOnly());

        $reflection = new \ReflectionProperty($params, 'filters');
        $this->assertTrue($reflection->isReadOnly());
    }
}
