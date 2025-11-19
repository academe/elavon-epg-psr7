<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\Shopper;

use Academe\Elavon\Epg\Psr7\Messages\Request\Shopper\RetrieveShopperListRequest;
use PHPUnit\Framework\TestCase;

class RetrieveShopperListRequestTest extends TestCase
{
    public function test_construct_withNoParameters_createsInstance(): void
    {
        $request = new RetrieveShopperListRequest();

        $this->assertSame([], $request->getQueryParams());
    }

    public function test_construct_withParams_createsInstance(): void
    {
        $params = ['limit' => 50, 'offset' => 100];
        $request = new RetrieveShopperListRequest($params);

        $this->assertSame($params, $request->getQueryParams());
    }

    public function test_build_createsValidPsr7Request(): void
    {
        $request = new RetrieveShopperListRequest();
        $psr7Request = $request->build();

        $this->assertSame('GET', $psr7Request->getMethod());
        $this->assertStringContainsString('/shoppers', (string) $psr7Request->getUri());
    }

    public function test_build_includesLimitInQueryString(): void
    {
        $params = ['limit' => 25];
        $request = new RetrieveShopperListRequest($params);
        $psr7Request = $request->build();

        $uri = (string) $psr7Request->getUri();

        $this->assertStringContainsString('limit=25', $uri);
    }
}
