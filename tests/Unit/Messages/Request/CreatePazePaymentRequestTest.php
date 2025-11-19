<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request;

use Academe\Elavon\Epg\Psr7\Dtos\PazePayment;
use Academe\Elavon\Epg\Psr7\Messages\Request\CreatePazePaymentRequest;
use PHPUnit\Framework\TestCase;

class CreatePazePaymentRequestTest extends TestCase
{
    public function test_construct_withPazePaymentObject_createsInstance(): void
    {
        $payment = new PazePayment(
            token: 'encrypted_token_data',
            customReference: 'ref123',
        );

        $request = new CreatePazePaymentRequest($payment);

        $this->assertSame($payment, $request->getPazePayment());
    }

    public function test_construct_withArray_normalizes(): void
    {
        $data = [
            'token' => 'encrypted_token',
            'customReference' => 'ref456',
        ];

        $request = new CreatePazePaymentRequest($data);

        $this->assertInstanceOf(PazePayment::class, $request->getPazePayment());
    }

    public function test_build_createsValidPsr7Request(): void
    {
        $payment = new PazePayment(token: 'test_token');
        $request = new CreatePazePaymentRequest($payment);

        $psr7Request = $request->build();

        $this->assertSame('POST', $psr7Request->getMethod());
        $this->assertStringContainsString('/paze-payments', (string) $psr7Request->getUri());
        $this->assertSame('application/json', $psr7Request->getHeaderLine('Content-Type'));
        $this->assertSame('application/json', $psr7Request->getHeaderLine('Accept'));
    }

    public function test_build_includesPaymentDataInBody(): void
    {
        $payment = new PazePayment(
            token: 'encrypted_data',
            customReference: 'ref789',
        );

        $request = new CreatePazePaymentRequest($payment);
        $psr7Request = $request->build();

        $body = (string) $psr7Request->getBody();
        $data = json_decode($body, true);

        $this->assertSame('encrypted_data', $data['token']);
        $this->assertSame('ref789', $data['customReference']);
    }

    public function test_usesCustomBaseUri(): void
    {
        $payment = new PazePayment(token: 'token');
        $request = new CreatePazePaymentRequest(
            pazePayment: $payment,
            baseUri: 'https://custom.api.com',
        );

        $psr7Request = $request->build();

        $this->assertStringStartsWith('https://custom.api.com', (string) $psr7Request->getUri());
    }
}
