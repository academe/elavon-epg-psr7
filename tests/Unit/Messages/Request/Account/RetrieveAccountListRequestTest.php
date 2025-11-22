<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\Account;

use Academe\Elavon\Epg\Psr7\Messages\Request\Account\RetrieveAccountListRequest;
use PHPUnit\Framework\TestCase;

class RetrieveAccountListRequestTest extends TestCase
{
    public function test_construct_withNoParams_createsInstance(): void
    {
        $request = new RetrieveAccountListRequest();

        $this->assertSame([], $request->getQueryParams());
    }

    public function test_construct_withQueryParams_createsInstance(): void
    {
        $params = ['limit' => 50, 'pageToken' => 'abc123'];
        $request = new RetrieveAccountListRequest($params);

        $this->assertSame($params, $request->getQueryParams());
    }

    public function test_build_withNoParams_createsValidPsr7Request(): void
    {
        $request = new RetrieveAccountListRequest();

        $psr7Request = $request->build();

        $this->assertSame('GET', $psr7Request->getMethod());
        $this->assertStringContainsString('/accounts', (string) $psr7Request->getUri());
        $this->assertStringNotContainsString('?', (string) $psr7Request->getUri());
    }

    public function test_build_withQueryParams_includesParamsInUri(): void
    {
        $params = ['limit' => 100, 'pageToken' => 'xyz789'];
        $request = new RetrieveAccountListRequest($params);

        $psr7Request = $request->build();

        $uri = (string) $psr7Request->getUri();
        $this->assertStringContainsString('/accounts?', $uri);
        $this->assertStringContainsString('limit=100', $uri);
        $this->assertStringContainsString('pageToken=xyz789', $uri);
    }

    public function test_build_withMultipleQueryParams_encodesAllParams(): void
    {
        $params = [
            'limit' => 25,
            'pageToken' => 'token123',
            'filter' => 'active',
        ];
        $request = new RetrieveAccountListRequest($params);

        $psr7Request = $request->build();

        $uri = (string) $psr7Request->getUri();
        $this->assertStringContainsString('limit=25', $uri);
        $this->assertStringContainsString('pageToken=token123', $uri);
        $this->assertStringContainsString('filter=active', $uri);
    }

    public function test_getQueryParams_returnsCorrectParams(): void
    {
        $params = ['limit' => 10];
        $request = new RetrieveAccountListRequest($params);

        $this->assertSame($params, $request->getQueryParams());
    }
}
