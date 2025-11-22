<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\PaymentLink;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentLink;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\PaymentLink\UpdatePaymentLinkRequest;
use PHPUnit\Framework\TestCase;

class UpdatePaymentLinkRequestTest extends TestCase
{
    public function test_construct_withValidIdAndData_createsInstance(): void
    {
        $paymentLink = new PaymentLink(
            doCancel: true,
            customReference: 'Updated reference',
        );

        $request = new UpdatePaymentLinkRequest('pl123', $paymentLink);

        $this->assertSame('pl123', $request->getPaymentLinkId());
        $this->assertSame($paymentLink, $request->getPaymentLink());
    }

    public function test_construct_withArray_normalizesToPaymentLink(): void
    {
        $data = [
            'doCancel' => true,
            'customReference' => 'New reference',
        ];

        $request = new UpdatePaymentLinkRequest('pl456', $data);

        $this->assertInstanceOf(PaymentLink::class, $request->getPaymentLink());
        $this->assertTrue($request->getPaymentLink()->doCancel);
    }

    public function test_construct_withEmptyId_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PaymentLink ID cannot be empty');

        new UpdatePaymentLinkRequest('', ['doCancel' => true]);
    }

    public function test_build_createsValidPsr7Request(): void
    {
        $paymentLink = new PaymentLink(
            doCancel: false,
            customFields: ['status' => 'active'],
        );

        $request = new UpdatePaymentLinkRequest('pl789', $paymentLink);
        $psr7Request = $request->build();

        $this->assertSame('POST', $psr7Request->getMethod());
        $this->assertStringContainsString('/payment-links/pl789', (string) $psr7Request->getUri());
    }

    public function test_build_includesPaymentLinkDataInBody(): void
    {
        $paymentLink = new PaymentLink(
            doCancel: true,
            customReference: 'CANCELLED-123',
            customFields: ['reason' => 'Customer request'],
        );

        $request = new UpdatePaymentLinkRequest('pl999', $paymentLink);
        $psr7Request = $request->build();

        $body = (string) $psr7Request->getBody();
        $data = json_decode($body, true);

        $this->assertTrue($data['doCancel']);
        $this->assertSame('CANCELLED-123', $data['customReference']);
        $this->assertSame(['reason' => 'Customer request'], $data['customFields']);
    }
}
