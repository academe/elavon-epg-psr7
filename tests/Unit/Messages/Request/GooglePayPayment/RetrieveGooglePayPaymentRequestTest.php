<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\GooglePayPayment;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\GooglePayPayment\RetrieveGooglePayPaymentRequest;
use PHPUnit\Framework\TestCase;

class RetrieveGooglePayPaymentRequestTest extends TestCase
{
    public function test_construct_withValidId_createsInstance(): void
    {
        $request = new RetrieveGooglePayPaymentRequest('payment_123');

        $this->assertSame('payment_123', $request->getGooglePayPaymentId());
    }

    public function test_construct_withEmptyId_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Google Pay payment ID cannot be empty');

        new RetrieveGooglePayPaymentRequest('');
    }

    public function test_build_createsValidPsr7Request(): void
    {
        $request = new RetrieveGooglePayPaymentRequest('payment_456');
        $psr7Request = $request->build();

        $this->assertSame('GET', $psr7Request->getMethod());
        $this->assertStringContainsString('/google-pay-payments/payment_456', (string) $psr7Request->getUri());
        $this->assertSame('application/json', $psr7Request->getHeaderLine('Accept'));
    }

    public function test_build_includesPaymentIdInUri(): void
    {
        $request = new RetrieveGooglePayPaymentRequest('payment_789');
        $psr7Request = $request->build();

        $uri = (string) $psr7Request->getUri();

        $this->assertStringEndsWith('/payment_789', $uri);
    }

    public function test_requestHasNoBody(): void
    {
        $request = new RetrieveGooglePayPaymentRequest('payment_000');
        $psr7Request = $request->build();

        $body = (string) $psr7Request->getBody();

        $this->assertEmpty($body);
    }
}
