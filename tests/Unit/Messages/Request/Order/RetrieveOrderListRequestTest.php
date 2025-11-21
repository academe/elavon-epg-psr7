<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\Order;

use Academe\Elavon\Epg\Psr7\Messages\Request\Order\RetrieveOrderListRequest;
use PHPUnit\Framework\TestCase;

class RetrieveOrderListRequestTest extends TestCase
{
    public function test_construct_withNoParams_createsInstance(): void
    {
        $request = new RetrieveOrderListRequest();

        $this->assertSame([], $request->getQueryParams());
    }

    public function test_construct_withQueryParams_createsInstance(): void
    {
        $params = ['limit' => 50, 'offset' => 100];

        $request = new RetrieveOrderListRequest($params);

        $this->assertSame($params, $request->getQueryParams());
    }

    public function test_build_withNoParams_createsValidPsr7Request(): void
    {
        $request = new RetrieveOrderListRequest();

        $psr7Request = $request->build();

        $this->assertSame('GET', $psr7Request->getMethod());
        $this->assertStringEndsWith('/orders', (string) $psr7Request->getUri());
        $this->assertSame('application/json', $psr7Request->getHeaderLine('Accept'));
    }

    public function test_build_withQueryParams_includesParamsInUri(): void
    {
        $request = new RetrieveOrderListRequest(['limit' => 25, 'offset' => 50]);

        $psr7Request = $request->build();

        $uri = (string) $psr7Request->getUri();
        $this->assertStringContainsString('limit=25', $uri);
        $this->assertStringContainsString('offset=50', $uri);
    }

    public function test_build_withCustomBaseUri_usesCustomUri(): void
    {
        $customUri = 'https://custom.api.example.com';
        $request = new RetrieveOrderListRequest([], baseUri: $customUri);

        $psr7Request = $request->build();

        $this->assertStringStartsWith($customUri, (string) $psr7Request->getUri());
    }
}
