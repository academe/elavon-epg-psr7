<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\GooglePayPayment;

use Academe\Elavon\Epg\Psr7\Dtos\GooglePayPayment;
use Academe\Elavon\Epg\Psr7\Messages\Request\GooglePayPayment\CreateGooglePayPaymentRequest;
use PHPUnit\Framework\TestCase;

class CreateGooglePayPaymentRequestTest extends TestCase
{
    public function test_construct_withGooglePayPaymentObject_createsInstance(): void
    {
        $payment = new GooglePayPayment(
            token: 'encrypted_token_data',
            customReference: 'ref123',
        );

        $request = new CreateGooglePayPaymentRequest($payment);

        $this->assertSame($payment, $request->googlePayPayment);
    }

    public function test_fromData_withArray_normalizes(): void
    {
        $data = [
            'token' => 'encrypted_token',
            'customReference' => 'ref456',
        ];

        $request = CreateGooglePayPaymentRequest::fromData(['googlePayPayment' => $data]);

        $this->assertInstanceOf(GooglePayPayment::class, $request->googlePayPayment);
    }

    public function test_build_createsValidPsr7Request(): void
    {
        $payment = new GooglePayPayment(token: 'test_token');
        $request = new CreateGooglePayPaymentRequest($payment);

        $psr7Request = $request->build();

        $this->assertSame('POST', $psr7Request->getMethod());
        $this->assertStringContainsString('/google-pay-payments', (string) $psr7Request->getUri());
    }

    public function test_build_includesPaymentDataInBody(): void
    {
        $payment = new GooglePayPayment(
            token: 'encrypted_data',
            customReference: 'ref789',
        );

        $request = new CreateGooglePayPaymentRequest($payment);
        $psr7Request = $request->build();

        $body = (string) $psr7Request->getBody();
        $data = json_decode($body, true);

        $this->assertSame('encrypted_data', $data['token']);
        $this->assertSame('ref789', $data['customReference']);
    }
}
