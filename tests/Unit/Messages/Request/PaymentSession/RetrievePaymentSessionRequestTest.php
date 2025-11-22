<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\PaymentSession;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\PaymentSession\RetrievePaymentSessionRequest;
use PHPUnit\Framework\TestCase;

class RetrievePaymentSessionRequestTest extends TestCase
{
    public function test_construct_withValidId_createsInstance(): void
    {
        $request = new RetrievePaymentSessionRequest('ps123');

        $this->assertSame('ps123', $request->getPaymentSessionId());
    }

    public function test_construct_withEmptyId_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PaymentSession ID cannot be empty');

        new RetrievePaymentSessionRequest('');
    }

    public function test_build_createsValidPsr7Request(): void
    {
        $request = new RetrievePaymentSessionRequest('ps123');
        $psr7Request = $request->build();

        $this->assertSame('GET', $psr7Request->getMethod());
        $this->assertStringContainsString('/payment-sessions/ps123', (string) $psr7Request->getUri());
    }

    public function test_build_hasNoBody(): void
    {
        $request = new RetrievePaymentSessionRequest('ps789');
        $psr7Request = $request->build();

        $body = (string) $psr7Request->getBody();
        $this->assertEmpty($body);
    }
}
