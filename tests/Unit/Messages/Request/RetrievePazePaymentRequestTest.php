<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\RetrievePazePaymentRequest;
use PHPUnit\Framework\TestCase;

class RetrievePazePaymentRequestTest extends TestCase
{
    public function test_construct_withValidId_createsInstance(): void
    {
        $request = new RetrievePazePaymentRequest('payment_123');

        $this->assertSame('payment_123', $request->getPazePaymentId());
    }

    public function test_construct_withEmptyId_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Paze payment ID cannot be empty');

        new RetrievePazePaymentRequest('');
    }

    public function test_build_createsValidPsr7Request(): void
    {
        $request = new RetrievePazePaymentRequest('payment_456');
        $psr7Request = $request->build();

        $this->assertSame('GET', $psr7Request->getMethod());
        $this->assertStringContainsString('/paze-payments/payment_456', (string) $psr7Request->getUri());
        $this->assertSame('application/json', $psr7Request->getHeaderLine('Accept'));
    }

    public function test_build_includesPaymentIdInUri(): void
    {
        $request = new RetrievePazePaymentRequest('payment_789');
        $psr7Request = $request->build();

        $uri = (string) $psr7Request->getUri();

        $this->assertStringEndsWith('/payment_789', $uri);
    }

    public function test_usesCustomBaseUri(): void
    {
        $request = new RetrievePazePaymentRequest(
            pazePaymentId: 'payment_999',
            baseUri: 'https://custom.api.com',
        );

        $psr7Request = $request->build();

        $this->assertStringStartsWith('https://custom.api.com', (string) $psr7Request->getUri());
    }

    public function test_requestHasNoBody(): void
    {
        $request = new RetrievePazePaymentRequest('payment_000');
        $psr7Request = $request->build();

        $body = (string) $psr7Request->getBody();

        $this->assertEmpty($body);
    }
}
