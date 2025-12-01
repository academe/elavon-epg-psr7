<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\PaymentLink;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentLink;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\PaymentLink\CreatePaymentLinkRequest;
use Academe\Elavon\Epg\Psr7\ValueObjects\CustomFields;
use Money\Money;
use PHPUnit\Framework\TestCase;

class CreatePaymentLinkRequestTest extends TestCase
{
    public function test_construct_withPaymentLinkObject_createsInstance(): void
    {
        $paymentLink = new PaymentLink(
            total: Money::USD(10000),
            expiresAt: '2025-12-31T23:59:59Z',
        );

        $request = new CreatePaymentLinkRequest($paymentLink);

        $this->assertSame($paymentLink, $request->getPaymentLink());
    }

    public function test_construct_withArray_normalizesToPaymentLink(): void
    {
        $data = [
            'total' => ['amount' => '150.00', 'currencyCode' => 'EUR'],
            'expiresAt' => '2025-12-31T23:59:59Z',
            'description' => 'Array payment link',
        ];

        $request = new CreatePaymentLinkRequest($data);

        $this->assertInstanceOf(PaymentLink::class, $request->getPaymentLink());
        $this->assertSame('15000', $request->getPaymentLink()->total->getAmount());
    }

    public function test_construct_withoutTotal_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PaymentLink total is required for creating a payment link');

        new CreatePaymentLinkRequest([
            'expiresAt' => '2025-12-31T23:59:59Z',
            'description' => 'No total',
        ]);
    }

    public function test_construct_withoutExpiresAt_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PaymentLink expiresAt is required for creating a payment link');

        new CreatePaymentLinkRequest([
            'total' => ['amount' => '100.00', 'currencyCode' => 'USD'],
            'description' => 'No expiresAt',
        ]);
    }

    public function test_build_createsValidPsr7Request(): void
    {
        $paymentLink = new PaymentLink(
            total: Money::GBP(20000),
            expiresAt: '2025-12-31T23:59:59Z',
        );
        $request = new CreatePaymentLinkRequest($paymentLink);

        $psr7Request = $request->build();

        $this->assertSame('POST', $psr7Request->getMethod());
        $this->assertStringContainsString('/payment-links', (string) $psr7Request->getUri());
    }

    public function test_build_includesPaymentLinkDataInBody(): void
    {
        $paymentLink = new PaymentLink(
            total: Money::USD(7550),
            expiresAt: '2025-12-31T23:59:59Z',
            description: 'Premium service payment',
            returnUrl: 'https://merchant.com/return',
            shopperEmailAddress: 'customer@example.com',
        );

        $request = new CreatePaymentLinkRequest($paymentLink);
        $psr7Request = $request->build();

        $body = (string) $psr7Request->getBody();
        $data = json_decode($body, true);

        $this->assertSame('75.50', $data['total']['amount']);
        $this->assertSame('USD', $data['total']['currencyCode']);
        $this->assertSame('2025-12-31T23:59:59Z', $data['expiresAt']);
        $this->assertSame('Premium service payment', $data['description']);
        $this->assertSame('https://merchant.com/return', $data['returnUrl']);
        $this->assertSame('customer@example.com', $data['shopperEmailAddress']);
    }

    public function test_build_withCustomFields_includesCustomFieldsInBody(): void
    {
        $paymentLink = new PaymentLink(
            total: Money::USD(30000),
            expiresAt: '2025-12-31T23:59:59Z',
            customFields: new CustomFields(['invoiceNumber' => 'INV-12345', 'project' => 'Alpha']),
        );

        $request = new CreatePaymentLinkRequest($paymentLink);
        $psr7Request = $request->build();

        $body = (string) $psr7Request->getBody();
        $data = json_decode($body, true);

        $this->assertArrayHasKey('customFields', $data);
        $this->assertSame('INV-12345', $data['customFields']['invoiceNumber']);
        $this->assertSame('Alpha', $data['customFields']['project']);
    }
}
