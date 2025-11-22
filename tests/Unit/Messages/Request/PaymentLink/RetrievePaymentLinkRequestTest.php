<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\PaymentLink;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\PaymentLink\RetrievePaymentLinkRequest;
use PHPUnit\Framework\TestCase;

class RetrievePaymentLinkRequestTest extends TestCase
{
    public function test_construct_withValidId_createsInstance(): void
    {
        $request = new RetrievePaymentLinkRequest('pl123');

        $this->assertSame('pl123', $request->getPaymentLinkId());
    }

    public function test_construct_withEmptyId_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PaymentLink ID cannot be empty');

        new RetrievePaymentLinkRequest('');
    }

    public function test_build_createsValidPsr7Request(): void
    {
        $request = new RetrievePaymentLinkRequest('pl456');
        $psr7Request = $request->build();

        $this->assertSame('GET', $psr7Request->getMethod());
        $this->assertStringContainsString('/payment-links/pl456', (string) $psr7Request->getUri());
    }

    public function test_build_includesPaymentLinkIdInUri(): void
    {
        $request = new RetrievePaymentLinkRequest('6xxFwvM8BqmM6T6DcF3DyTB3');
        $psr7Request = $request->build();

        $uri = (string) $psr7Request->getUri();
        $this->assertStringEndsWith('/payment-links/6xxFwvM8BqmM6T6DcF3DyTB3', $uri);
    }
}
