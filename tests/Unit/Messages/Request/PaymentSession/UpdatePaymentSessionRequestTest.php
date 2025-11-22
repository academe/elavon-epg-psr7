<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\PaymentSession;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentSession;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\PaymentSession\UpdatePaymentSessionRequest;
use PHPUnit\Framework\TestCase;

class UpdatePaymentSessionRequestTest extends TestCase
{
    public function test_construct_withValidIdAndPaymentSession_createsInstance(): void
    {
        $paymentSession = new PaymentSession(
            doReset: true,
        );

        $request = new UpdatePaymentSessionRequest('ps123', $paymentSession);

        $this->assertSame('ps123', $request->getPaymentSessionId());
        $this->assertSame($paymentSession, $request->getPaymentSession());
    }

    public function test_construct_withEmptyId_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PaymentSession ID cannot be empty');

        new UpdatePaymentSessionRequest('', new PaymentSession());
    }

    public function test_construct_withArray_normalizesToPaymentSession(): void
    {
        $data = [
            'customReference' => 'UPDATED-REF',
            'doReset' => true,
        ];

        $request = new UpdatePaymentSessionRequest('ps456', $data);

        $this->assertInstanceOf(PaymentSession::class, $request->getPaymentSession());
        $this->assertSame('UPDATED-REF', $request->getPaymentSession()->customReference);
        $this->assertTrue($request->getPaymentSession()->doReset);
    }

    public function test_build_createsValidPsr7Request(): void
    {
        $paymentSession = new PaymentSession(
            doReset: true,
        );
        $request = new UpdatePaymentSessionRequest('ps123', $paymentSession);

        $psr7Request = $request->build();

        $this->assertSame('POST', $psr7Request->getMethod());
        $this->assertStringContainsString('/payment-sessions/ps123', (string) $psr7Request->getUri());
    }

    public function test_build_includesPaymentSessionDataInBody(): void
    {
        $paymentSession = new PaymentSession(
            customReference: 'UPDATED-123',
            doReset: true,
            shopperLanguageTag: 'fr-FR',
        );

        $request = new UpdatePaymentSessionRequest('ps789', $paymentSession);
        $psr7Request = $request->build();

        $body = (string) $psr7Request->getBody();
        $data = json_decode($body, true);

        $this->assertSame('UPDATED-123', $data['customReference']);
        $this->assertTrue($data['doReset']);
        $this->assertSame('fr-FR', $data['shopperLanguageTag']);
    }
}
