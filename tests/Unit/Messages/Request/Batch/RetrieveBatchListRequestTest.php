<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\Batch;

use Academe\Elavon\Epg\Psr7\Messages\Request\Batch\RetrieveBatchListRequest;
use PHPUnit\Framework\TestCase;

class RetrieveBatchListRequestTest extends TestCase
{
    public function test_construct_withNoParams_createsInstance(): void
    {
        $request = new RetrieveBatchListRequest();

        $this->assertSame([], $request->getQueryParams());
    }

    public function test_construct_withQueryParams_createsInstance(): void
    {
        $params = ['limit' => 50, 'pageToken' => 'abc123'];
        $request = new RetrieveBatchListRequest($params);

        $this->assertSame($params, $request->getQueryParams());
    }

    public function test_build_withNoParams_createsValidPsr7Request(): void
    {
        $request = new RetrieveBatchListRequest();

        $psr7Request = $request->build();

        $this->assertSame('GET', $psr7Request->getMethod());
        $this->assertStringContainsString('/batches', (string) $psr7Request->getUri());
        $this->assertStringNotContainsString('?', (string) $psr7Request->getUri());
        $this->assertSame('application/json', $psr7Request->getHeaderLine('Accept'));
    }

    public function test_build_withQueryParams_includesParamsInUri(): void
    {
        $params = ['limit' => 25, 'pageToken' => 'xyz789'];
        $request = new RetrieveBatchListRequest($params);

        $psr7Request = $request->build();

        $uri = (string) $psr7Request->getUri();
        $this->assertStringContainsString('/batches?', $uri);
        $this->assertStringContainsString('limit=25', $uri);
        $this->assertStringContainsString('pageToken=xyz789', $uri);
    }

    public function test_build_withLimitOnly_includesLimitInUri(): void
    {
        $params = ['limit' => 10];
        $request = new RetrieveBatchListRequest($params);

        $psr7Request = $request->build();

        $uri = (string) $psr7Request->getUri();
        $this->assertStringContainsString('limit=10', $uri);
    }

    public function test_build_withPageTokenOnly_includesPageTokenInUri(): void
    {
        $params = ['pageToken' => 'next-page-token'];
        $request = new RetrieveBatchListRequest($params);

        $psr7Request = $request->build();

        $uri = (string) $psr7Request->getUri();
        $this->assertStringContainsString('pageToken=next-page-token', $uri);
    }

    public function test_getQueryParams_returnsCorrectParams(): void
    {
        $params = ['limit' => 100, 'pageToken' => 'token123'];
        $request = new RetrieveBatchListRequest($params);

        $this->assertSame($params, $request->getQueryParams());
    }

    public function test_build_withMultipleParams_includesAllInUri(): void
    {
        $params = [
            'limit' => 50,
            'pageToken' => 'abc',
            'customParam' => 'value',
        ];
        $request = new RetrieveBatchListRequest($params);

        $psr7Request = $request->build();

        $uri = (string) $psr7Request->getUri();
        $this->assertStringContainsString('limit=50', $uri);
        $this->assertStringContainsString('pageToken=abc', $uri);
        $this->assertStringContainsString('customParam=value', $uri);
    }
}
