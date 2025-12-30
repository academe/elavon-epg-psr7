<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\PaymentLink;

use Academe\Elavon\Epg\Psr7\Dtos\QueryParams;
use Academe\Elavon\Epg\Psr7\Enums\QueryFilterOperator;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\PaymentLink\RetrievePaymentLinkEventListRequest;
use PHPUnit\Framework\TestCase;

class RetrievePaymentLinkEventListRequestTest extends TestCase
{
    public function test_construct_withValidIdNoParams_createsInstance(): void
    {
        $request = new RetrievePaymentLinkEventListRequest('pl123');

        $this->assertSame('pl123', $request->paymentLinkId);
        $this->assertTrue($request->queryParams->isEmpty());
    }

    public function test_construct_withValidIdAndParams_createsInstance(): void
    {
        $params = QueryParams::create()->withLimit(50)->withPageToken('abc123');
        $request = new RetrievePaymentLinkEventListRequest('pl456', $params);

        $this->assertSame('pl456', $request->paymentLinkId);
        $this->assertSame(50, $request->queryParams->limit);
        $this->assertSame('abc123', $request->queryParams->pageToken);
    }

    public function test_construct_withEmptyId_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PaymentLink ID cannot be empty');

        new RetrievePaymentLinkEventListRequest('');
    }

    public function test_build_createsValidPsr7Request(): void
    {
        $request = new RetrievePaymentLinkEventListRequest('pl789');
        $psr7Request = $request->build();

        $this->assertSame('GET', $psr7Request->getMethod());
        $this->assertStringContainsString('/payment-links/pl789/payment-link-events', (string) $psr7Request->getUri());
    }

    public function test_build_withQueryParams_includesParamsInUri(): void
    {
        $params = QueryParams::create()->withLimit(25)->withPageToken('nextPage');
        $request = new RetrievePaymentLinkEventListRequest('pl999', $params);
        $psr7Request = $request->build();

        $uri = (string) $psr7Request->getUri();
        $this->assertStringContainsString('limit=25', $uri);
        $this->assertStringContainsString('pageToken=nextPage', $uri);
    }

    public function test_build_withFilters_includesFiltersInUri(): void
    {
        $params = QueryParams::create()
            ->withLimit(10)
            ->withFilter('createdAt', QueryFilterOperator::GT, '2024-01-01');
        $request = new RetrievePaymentLinkEventListRequest('pl888', $params);

        $psr7Request = $request->build();

        $uri = (string) $psr7Request->getUri();
        $this->assertStringContainsString('limit=10', $uri);
        $this->assertStringContainsString('filter=createdAt_gt_2024-01-01', $uri);
    }

    public function test_build_withNoParams_excludesQueryString(): void
    {
        $request = new RetrievePaymentLinkEventListRequest('pl111');
        $psr7Request = $request->build();

        $uri = (string) $psr7Request->getUri();
        $this->assertStringEndsWith('/payment-link-events', $uri);
    }

    public function test_fromData_withArray_parsesQueryParams(): void
    {
        $data = [
            'paymentLinkId' => 'pl555',
            'queryParams' => [
                'limit' => 50,
                'pageToken' => 'token123',
            ],
        ];

        $request = RetrievePaymentLinkEventListRequest::fromData($data);

        $this->assertSame('pl555', $request->paymentLinkId);
        $this->assertSame(50, $request->queryParams->limit);
        $this->assertSame('token123', $request->queryParams->pageToken);
    }

    public function test_fromData_withQueryParamsObject_usesDirectly(): void
    {
        $params = QueryParams::create()->withLimit(100);
        $data = [
            'paymentLinkId' => 'pl666',
            'queryParams' => $params,
        ];

        $request = RetrievePaymentLinkEventListRequest::fromData($data);

        $this->assertSame('pl666', $request->paymentLinkId);
        $this->assertSame(100, $request->queryParams->limit);
    }

    public function test_fromData_withEmptyQueryParams_createsEmptyParams(): void
    {
        $data = ['paymentLinkId' => 'pl777'];

        $request = RetrievePaymentLinkEventListRequest::fromData($data);

        $this->assertSame('pl777', $request->paymentLinkId);
        $this->assertTrue($request->queryParams->isEmpty());
    }
}
