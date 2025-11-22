<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\PaymentLink;

use Academe\Elavon\Epg\Psr7\Messages\Request\PaymentLink\RetrievePaymentLinkListRequest;
use PHPUnit\Framework\TestCase;

class RetrievePaymentLinkListRequestTest extends TestCase
{
    public function test_construct_withNoParams_createsInstance(): void
    {
        $request = new RetrievePaymentLinkListRequest();

        $this->assertSame([], $request->getQueryParams());
    }

    public function test_construct_withQueryParams_createsInstance(): void
    {
        $params = ['limit' => 50, 'offset' => 100];
        $request = new RetrievePaymentLinkListRequest($params);

        $this->assertSame($params, $request->getQueryParams());
    }

    public function test_build_createsValidPsr7Request(): void
    {
        $request = new RetrievePaymentLinkListRequest();
        $psr7Request = $request->build();

        $this->assertSame('GET', $psr7Request->getMethod());
        $this->assertStringContainsString('/payment-links', (string) $psr7Request->getUri());
    }

    public function test_build_withQueryParams_includesParamsInUri(): void
    {
        $params = ['limit' => 25, 'offset' => 50];
        $request = new RetrievePaymentLinkListRequest($params);
        $psr7Request = $request->build();

        $uri = (string) $psr7Request->getUri();
        $this->assertStringContainsString('limit=25', $uri);
        $this->assertStringContainsString('offset=50', $uri);
    }

    public function test_build_withNoParams_excludesQueryString(): void
    {
        $request = new RetrievePaymentLinkListRequest();
        $psr7Request = $request->build();

        $uri = (string) $psr7Request->getUri();
        $this->assertStringEndsWith('/payment-links', $uri);
        $this->assertStringNotContainsString('?', $uri);
    }
}
