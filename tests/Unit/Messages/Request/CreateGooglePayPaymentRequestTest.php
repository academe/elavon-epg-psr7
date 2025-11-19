<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request;

use Academe\Elavon\Epg\Psr7\Dtos\GooglePayPayment;
use Academe\Elavon\Epg\Psr7\Messages\Request\CreateGooglePayPaymentRequest;
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

        $this->assertSame($payment, $request->getGooglePayPayment());
    }

    public function test_construct_withArray_normalizes(): void
    {
        $data = [
            'token' => 'encrypted_token',
            'customReference' => 'ref456',
        ];

        $request = new CreateGooglePayPaymentRequest($data);

        $this->assertInstanceOf(GooglePayPayment::class, $request->getGooglePayPayment());
    }

    public function test_build_createsValidPsr7Request(): void
    {
        $payment = new GooglePayPayment(token: 'test_token');
        $request = new CreateGooglePayPaymentRequest($payment);

        $psr7Request = $request->build();

        $this->assertSame('POST', $psr7Request->getMethod());
        $this->assertStringContainsString('/google-pay-payments', (string) $psr7Request->getUri());
        $this->assertSame('application/json', $psr7Request->getHeaderLine('Content-Type'));
        $this->assertSame('application/json', $psr7Request->getHeaderLine('Accept'));
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

    public function test_usesCustomBaseUri(): void
    {
        $payment = new GooglePayPayment(token: 'token');
        $request = new CreateGooglePayPaymentRequest(
            googlePayPayment: $payment,
            baseUri: 'https://custom.api.com',
        );

        $psr7Request = $request->build();

        $this->assertStringStartsWith('https://custom.api.com', (string) $psr7Request->getUri());
    }
}
