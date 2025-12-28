<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\PaymentSession;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentSession;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\PaymentSession\CreatePaymentSessionRequest;
use Money\Money;
use PHPUnit\Framework\TestCase;

class CreatePaymentSessionRequestTest extends TestCase
{
    public function test_construct_withPaymentSessionObject_createsInstance(): void
    {
        $paymentSession = new PaymentSession(
            order: 'https://api.example.com/orders/ord123',
            returnUrl: 'https://merchant.com/return',
        );

        $request = new CreatePaymentSessionRequest($paymentSession);

        $this->assertSame($paymentSession, $request->paymentSession);
    }

    public function test_fromData_withArray_normalizesToPaymentSession(): void
    {
        $data = [
            'order' => 'https://api.example.com/orders/ord456',
            'returnUrl' => 'https://merchant.com/return',
            'cancelUrl' => 'https://merchant.com/cancel',
        ];

        $request = CreatePaymentSessionRequest::fromData(['paymentSession' => $data]);

        $this->assertInstanceOf(PaymentSession::class, $request->paymentSession);
        $this->assertSame('https://api.example.com/orders/ord456', $request->paymentSession->order);
    }

    public function test_fromData_withoutOrder_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Order is required for creating a payment session');

        CreatePaymentSessionRequest::fromData(['paymentSession' => ['returnUrl' => 'https://merchant.com/return']]);
    }

    public function test_build_createsValidPsr7Request(): void
    {
        $paymentSession = new PaymentSession(
            order: 'https://api.example.com/orders/ord123',
        );
        $request = new CreatePaymentSessionRequest($paymentSession);

        $psr7Request = $request->build();

        $this->assertSame('POST', $psr7Request->getMethod());
        $this->assertStringContainsString('/payment-sessions', (string) $psr7Request->getUri());
    }

    public function test_build_includesPaymentSessionDataInBody(): void
    {
        $paymentSession = new PaymentSession(
            order: 'https://api.example.com/orders/ord123',
            returnUrl: 'https://merchant.com/return',
            cancelUrl: 'https://merchant.com/cancel',
            shopperEmailAddress: new \Academe\Elavon\Epg\Psr7\ValueObjects\EmailAddress('shopper@example.com'),
            doCreateTransaction: true,
        );

        $request = new CreatePaymentSessionRequest($paymentSession);
        $psr7Request = $request->build();

        $body = (string) $psr7Request->getBody();
        $data = json_decode($body, true);

        $this->assertSame('https://api.example.com/orders/ord123', $data['order']);
        $this->assertSame('https://merchant.com/return', $data['returnUrl']);
        $this->assertSame('https://merchant.com/cancel', $data['cancelUrl']);
        $this->assertSame('shopper@example.com', $data['shopperEmailAddress']);
        $this->assertTrue($data['doCreateTransaction']);
    }

    public function test_build_withBillToAndShipTo_includesContactsInBody(): void
    {
        $paymentSession = PaymentSession::fromData([
            'order' => 'https://api.example.com/orders/ord123',
            'billTo' => [
                'fullName' => 'John Doe',
                'street1' => '123 Main St',
                'city' => 'New York',
            ],
            'shipTo' => [
                'fullName' => 'Jane Smith',
                'street1' => '456 Oak Ave',
                'city' => 'Boston',
            ],
        ]);

        $request = new CreatePaymentSessionRequest($paymentSession);
        $psr7Request = $request->build();

        $body = (string) $psr7Request->getBody();
        $data = json_decode($body, true);

        $this->assertIsArray($data['billTo']);
        $this->assertSame('John Doe', $data['billTo']['fullName']);
        $this->assertSame('123 Main St', $data['billTo']['street1']);
        $this->assertIsArray($data['shipTo']);
        $this->assertSame('Jane Smith', $data['shipTo']['fullName']);
        $this->assertSame('456 Oak Ave', $data['shipTo']['street1']);
    }

    public function test_build_withSalesTax_includesTaxInBody(): void
    {
        $paymentSession = new PaymentSession(
            order: 'https://api.example.com/orders/ord123',
            salesTax: Money::USD(1050),
        );

        $request = new CreatePaymentSessionRequest($paymentSession);
        $psr7Request = $request->build();

        $body = (string) $psr7Request->getBody();
        $data = json_decode($body, true);

        $this->assertIsArray($data['salesTax']);
        $this->assertSame('10.50', $data['salesTax']['amount']);
        $this->assertSame('USD', $data['salesTax']['currencyCode']);
    }
}
