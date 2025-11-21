<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\PaymentLink;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\PaymentLink\RetrievePaymentLinkEventListRequest;
use PHPUnit\Framework\TestCase;

class RetrievePaymentLinkEventListRequestTest extends TestCase
{
    public function test_construct_withValidIdNoParams_createsInstance(): void
    {
        $request = new RetrievePaymentLinkEventListRequest('pl123');

        $this->assertSame('pl123', $request->getPaymentLinkId());
        $this->assertSame([], $request->getQueryParams());
    }

    public function test_construct_withValidIdAndParams_createsInstance(): void
    {
        $params = ['limit' => 50];
        $request = new RetrievePaymentLinkEventListRequest('pl456', $params);

        $this->assertSame('pl456', $request->getPaymentLinkId());
        $this->assertSame($params, $request->getQueryParams());
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
        $this->assertSame('application/json', $psr7Request->getHeaderLine('Accept'));
    }

    public function test_build_withQueryParams_includesParamsInUri(): void
    {
        $params = ['limit' => 25, 'offset' => 50];
        $request = new RetrievePaymentLinkEventListRequest('pl999', $params);
        $psr7Request = $request->build();

        $uri = (string) $psr7Request->getUri();
        $this->assertStringContainsString('limit=25', $uri);
        $this->assertStringContainsString('offset=50', $uri);
    }

    public function test_build_withNoParams_excludesQueryString(): void
    {
        $request = new RetrievePaymentLinkEventListRequest('pl111');
        $psr7Request = $request->build();

        $uri = (string) $psr7Request->getUri();
        $this->assertStringEndsWith('/payment-link-events', $uri);
    }
}
