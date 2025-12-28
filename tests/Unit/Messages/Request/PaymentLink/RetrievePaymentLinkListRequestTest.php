<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\PaymentLink;

use Academe\Elavon\Epg\Psr7\Dtos\QueryParams;
use Academe\Elavon\Epg\Psr7\Messages\Request\PaymentLink\RetrievePaymentLinkListRequest;
use PHPUnit\Framework\TestCase;

class RetrievePaymentLinkListRequestTest extends TestCase
{
    public function test_construct_withNoParams_createsInstance(): void
    {
        $request = new RetrievePaymentLinkListRequest();

        $this->assertTrue($request->queryParams->isEmpty());
    }

    public function test_construct_withQueryParams_createsInstance(): void
    {
        $params = QueryParams::create()->withLimit(50)->withPageToken('abc123');

        $request = new RetrievePaymentLinkListRequest($params);

        $this->assertSame(50, $request->queryParams->limit);
        $this->assertSame('abc123', $request->queryParams->pageToken);
    }

    public function test_build_withNoParams_createsValidPsr7Request(): void
    {
        $request = new RetrievePaymentLinkListRequest();

        $psr7Request = $request->build();

        $this->assertSame('GET', $psr7Request->getMethod());
        $this->assertSame('/payment-links', (string) $psr7Request->getUri());
    }

    public function test_build_withQueryParams_includesParamsInUri(): void
    {
        $params = QueryParams::create()->withLimit(25)->withPageToken('nextPage');
        $request = new RetrievePaymentLinkListRequest($params);

        $psr7Request = $request->build();

        $uri = (string) $psr7Request->getUri();
        $this->assertStringContainsString('limit=25', $uri);
        $this->assertStringContainsString('pageToken=nextPage', $uri);
    }

    public function test_build_withFilters_includesFiltersInUri(): void
    {
        $params = QueryParams::create()
            ->withLimit(10)
            ->withFilter('createdAt', 'gt', '2024-01-01');
        $request = new RetrievePaymentLinkListRequest($params);

        $psr7Request = $request->build();

        $uri = (string) $psr7Request->getUri();
        $this->assertStringContainsString('limit=10', $uri);
        $this->assertStringContainsString('filter=createdAt_gt_2024-01-01', $uri);
    }

    public function test_fromData_withArray_parsesQueryParams(): void
    {
        $data = [
            'queryParams' => [
                'limit' => 50,
                'pageToken' => 'token123',
            ],
        ];

        $request = RetrievePaymentLinkListRequest::fromData($data);

        $this->assertSame(50, $request->queryParams->limit);
        $this->assertSame('token123', $request->queryParams->pageToken);
    }

    public function test_fromData_withQueryParamsObject_usesDirectly(): void
    {
        $params = QueryParams::create()->withLimit(100);
        $data = ['queryParams' => $params];

        $request = RetrievePaymentLinkListRequest::fromData($data);

        $this->assertSame(100, $request->queryParams->limit);
    }

    public function test_fromData_withEmptyData_createsEmptyParams(): void
    {
        $request = RetrievePaymentLinkListRequest::fromData([]);

        $this->assertTrue($request->queryParams->isEmpty());
    }
}
