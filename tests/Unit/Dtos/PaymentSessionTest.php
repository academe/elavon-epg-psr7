<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\Blik;
use Academe\Elavon\Epg\Psr7\Dtos\Contact;
use Academe\Elavon\Epg\Psr7\Dtos\DebtorAccount;
use Academe\Elavon\Epg\Psr7\Dtos\PaymentSession;
use Academe\Elavon\Epg\Psr7\Dtos\ThreeDSecure;
use Academe\Elavon\Epg\Psr7\Enums\HppType;
use Academe\Elavon\Epg\Psr7\Enums\PaymentMethod;
use Academe\Elavon\Epg\Psr7\Enums\PaymentMethodOrigin;
use Academe\Elavon\Epg\Psr7\Enums\ShopperInteraction;
use Academe\Elavon\Epg\Psr7\ValueObjects\CustomFields;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Money\Money;
use PHPUnit\Framework\TestCase;

/**
 * Tests for PaymentSession DTO.
 */
class PaymentSessionTest extends TestCase
{
    public function test_construct_withMinimalFields_createsInstance(): void
    {
        // Arrange & Act
        $paymentSession = new PaymentSession(
            order: 'https://api.example.com/orders/ord123'
        );

        // Assert
        $this->assertSame('https://api.example.com/orders/ord123', $paymentSession->order);
        $this->assertNull($paymentSession->id);
        $this->assertNull($paymentSession->salesTax);
        $this->assertNull($paymentSession->billTo);
        $this->assertNull($paymentSession->returnUrl);
    }

    public function test_construct_withAllFields_createsInstance(): void
    {
        // Arrange & Act
        $paymentSession = PaymentSession::fromData([
            'href' => 'https://api.example.com/payment-sessions/ps123',
            'id' => 'ps123',
            'createdAt' => '2025-11-19T10:00:00Z',
            'modifiedAt' => '2025-11-19T11:00:00Z',
            'expiresAt' => '2025-11-19T12:00:00Z',
            'merchant' => 'https://api.example.com/merchants/m123',
            'account' => 'https://api.example.com/accounts/a123',
            'url' => 'https://hpp.example.com/ps123',
            'order' => 'https://api.example.com/orders/ord123',
            'allowedPaymentMethods' => ['Card', 'BLIK'],
            'allowedPaymentMethodOrigins' => ['Card', 'Apple Pay'],
            'paymentLink' => 'https://api.example.com/payment-links/pl123',
            'salesTax' => Money::USD(1000),
            'tip' => Money::USD(500),
            'shopperEmailAddress' => 'shopper@example.com',
            'billTo' => ['fullName' => 'John Doe', 'street1' => '123 Main St'],
            'shipTo' => ['fullName' => 'Jane Doe', 'street1' => '456 Oak Ave'],
            'hppType' => 'fullPageRedirect',
            'returnUrl' => 'https://merchant.com/return',
            'cancelUrl' => 'https://merchant.com/cancel',
            'shopperInteraction' => 'ecommerce',
            'doCreateTransaction' => true,
            'doCapture' => true,
            'customReference' => 'REF-123',
            'customFields' => ['field1' => 'value1'],
        ]);

        // Assert
        $this->assertSame('ps123', $paymentSession->id);
        $this->assertSame('https://api.example.com/orders/ord123', $paymentSession->order);
        $this->assertInstanceOf(Money::class, $paymentSession->salesTax);
        $this->assertSame('1000', $paymentSession->salesTax->getAmount());
        $this->assertInstanceOf(Contact::class, $paymentSession->billTo);
        $this->assertSame('John Doe', $paymentSession->billTo->fullName);
        $this->assertInstanceOf(Contact::class, $paymentSession->shipTo);
        $this->assertSame('Jane Doe', $paymentSession->shipTo->fullName);
        $this->assertSame(HppType::FULL_PAGE_REDIRECT, $paymentSession->hppType);
        $this->assertSame(ShopperInteraction::ECOMMERCE, $paymentSession->shopperInteraction);
        $this->assertIsArray($paymentSession->allowedPaymentMethods);
        $this->assertCount(2, $paymentSession->allowedPaymentMethods);
        $this->assertSame(PaymentMethod::CARD, $paymentSession->allowedPaymentMethods[0]);
        $this->assertSame(PaymentMethod::BLIK, $paymentSession->allowedPaymentMethods[1]);
        $this->assertIsArray($paymentSession->allowedPaymentMethodOrigins);
        $this->assertCount(2, $paymentSession->allowedPaymentMethodOrigins);
        $this->assertSame(PaymentMethodOrigin::CARD, $paymentSession->allowedPaymentMethodOrigins[0]);
        $this->assertSame(PaymentMethodOrigin::APPLE_PAY, $paymentSession->allowedPaymentMethodOrigins[1]);
        $this->assertTrue($paymentSession->doCreateTransaction);
        $this->assertTrue($paymentSession->doCapture);
    }

    public function test_construct_withMoneyObjects_createsInstance(): void
    {
        // Arrange
        $salesTax = Money::EUR(1500); // 15.00 EUR
        $tip = Money::EUR(750); // 7.50 EUR

        // Act
        $paymentSession = new PaymentSession(
            order: 'https://api.example.com/orders/ord123',
            salesTax: $salesTax,
            tip: $tip,
        );

        // Assert
        $this->assertSame($salesTax, $paymentSession->salesTax);
        $this->assertSame($tip, $paymentSession->tip);
    }

    public function test_construct_withContactObjects_createsInstance(): void
    {
        // Arrange
        $billTo = new Contact(
            fullName: 'Alice Smith',
            street1: '789 Elm St',
        );
        $shipTo = new Contact(
            fullName: 'Bob Johnson',
            street1: '321 Pine St',
        );

        // Act
        $paymentSession = new PaymentSession(
            order: 'https://api.example.com/orders/ord123',
            billTo: $billTo,
            shipTo: $shipTo,
        );

        // Assert
        $this->assertSame($billTo, $paymentSession->billTo);
        $this->assertSame($shipTo, $paymentSession->shipTo);
    }

    public function test_construct_withBlikObject_createsInstance(): void
    {
        // Arrange
        $blik = new Blik('123456');

        // Act
        $paymentSession = new PaymentSession(
            order: 'https://api.example.com/orders/ord123',
            blik: $blik,
        );

        // Assert
        $this->assertSame($blik, $paymentSession->blik);
    }

    public function test_construct_withDebtorAccountObject_createsInstance(): void
    {
        // Arrange
        $debtorAccount = new DebtorAccount(
            dateOfBirth: '19900101',
            accountNumber: '1234567890',
            lastName: 'Smith',
        );

        // Act
        $paymentSession = new PaymentSession(
            order: 'https://api.example.com/orders/ord123',
            debtorAccount: $debtorAccount,
        );

        // Assert
        $this->assertSame($debtorAccount, $paymentSession->debtorAccount);
    }

    public function test_construct_withThreeDSecureObject_createsInstance(): void
    {
        // Arrange
        $threeDSecure = new ThreeDSecure(
            directoryServerTransactionId: '12345678-1234-5234-8234-123456789012',
            transactionStatus: 'Y',
            protocolVersion: '2.1.0',
        );

        // Act
        $paymentSession = new PaymentSession(
            order: 'https://api.example.com/orders/ord123',
            threeDSecure: $threeDSecure,
        );

        // Assert
        $this->assertSame($threeDSecure, $paymentSession->threeDSecure);
    }

    public function test_construct_withTooLongShopperEmail_throwsException(): void
    {
        // Arrange
        $longEmail = str_repeat('a', 255) . '@example.com';

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Shopper email address must not exceed 254 characters');

        // Act
        new PaymentSession(
            order: 'https://api.example.com/orders/ord123',
            shopperEmailAddress: $longEmail,
        );
    }

    public function test_construct_withTooLongReturnUrl_throwsException(): void
    {
        // Arrange
        $longUrl = 'https://example.com/' . str_repeat('a', 2048);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Return URL must not exceed 2048 characters');

        // Act
        new PaymentSession(
            order: 'https://api.example.com/orders/ord123',
            returnUrl: $longUrl,
        );
    }

    public function test_construct_withInvalidReturnUrl_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Return URL must be a valid HTTP/HTTPS URL');

        // Act
        new PaymentSession(
            order: 'https://api.example.com/orders/ord123',
            returnUrl: 'invalid-url',
        );
    }

    public function test_construct_withTooLongCancelUrl_throwsException(): void
    {
        // Arrange
        $longUrl = 'https://example.com/' . str_repeat('a', 2048);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cancel URL must not exceed 2048 characters');

        // Act
        new PaymentSession(
            order: 'https://api.example.com/orders/ord123',
            cancelUrl: $longUrl,
        );
    }

    public function test_construct_withInvalidCancelUrl_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cancel URL must be a valid HTTP/HTTPS URL');

        // Act
        new PaymentSession(
            order: 'https://api.example.com/orders/ord123',
            cancelUrl: 'ftp://invalid.com',
        );
    }

    public function test_construct_withTooLongOriginUrl_throwsException(): void
    {
        // Arrange
        $longUrl = 'https://example.com/' . str_repeat('a', 2048);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Origin URL must not exceed 2048 characters');

        // Act
        new PaymentSession(
            order: 'https://api.example.com/orders/ord123',
            originUrl: $longUrl,
        );
    }

    public function test_construct_withTooLongCustomReference_throwsException(): void
    {
        // Arrange
        $longRef = str_repeat('a', 256);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Custom reference must not exceed 255 characters');

        // Act
        new PaymentSession(
            order: 'https://api.example.com/orders/ord123',
            customReference: $longRef,
        );
    }

    public function test_construct_withInvalidCustomFieldName_throwsException(): void
    {
        // Arrange
        $longKey = str_repeat('a', 65);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Custom field name must not exceed 64 characters');

        // Act - CustomFields validates the key length
        new PaymentSession(
            order: 'https://api.example.com/orders/ord123',
            customFields: new CustomFields([$longKey => 'value']),
        );
    }

    public function test_construct_withInvalidCustomFieldValue_throwsException(): void
    {
        // Arrange
        $longValue = str_repeat('a', 1025);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Custom field value for "field1" must not exceed 1024 characters');

        // Act - CustomFields validates the value length
        new PaymentSession(
            order: 'https://api.example.com/orders/ord123',
            customFields: new CustomFields(['field1' => $longValue]),
        );
    }

    public function test_fromData_withMinimalData_createsInstance(): void
    {
        // Arrange
        $data = [
            'order' => 'https://api.example.com/orders/ord123',
        ];

        // Act
        $paymentSession = PaymentSession::fromData($data);

        // Assert
        $this->assertSame('https://api.example.com/orders/ord123', $paymentSession->order);
        $this->assertNull($paymentSession->id);
    }

    public function test_fromData_withFullData_createsInstance(): void
    {
        // Arrange
        $data = [
            'href' => 'https://api.example.com/payment-sessions/ps123',
            'id' => 'ps123',
            'order' => 'https://api.example.com/orders/ord123',
            'salesTax' => ['amount' => '20.00', 'currencyCode' => 'GBP'],
            'billTo' => [
                'fullName' => 'Carol White',
                'street1' => '555 Broadway',
            ],
            'hppType' => 'lightbox',
            'shopperInteraction' => 'merchantInitiated',
            'allowedPaymentMethods' => ['Card', 'ACH'],
            'doCreateTransaction' => false,
            'customReference' => 'CUSTOM-456',
        ];

        // Act
        $paymentSession = PaymentSession::fromData($data);

        // Assert
        $this->assertSame('ps123', $paymentSession->id);
        $this->assertInstanceOf(Money::class, $paymentSession->salesTax);
        $this->assertSame('2000', $paymentSession->salesTax->getAmount());
        $this->assertInstanceOf(Contact::class, $paymentSession->billTo);
        $this->assertSame('Carol White', $paymentSession->billTo->fullName);
        $this->assertSame(HppType::LIGHTBOX, $paymentSession->hppType);
        $this->assertSame(ShopperInteraction::MERCHANT_INITIATED, $paymentSession->shopperInteraction);
        $this->assertCount(2, $paymentSession->allowedPaymentMethods);
        $this->assertSame(PaymentMethod::CARD, $paymentSession->allowedPaymentMethods[0]);
        $this->assertSame(PaymentMethod::ACH, $paymentSession->allowedPaymentMethods[1]);
        $this->assertSame(false, $paymentSession->doCreateTransaction);
    }

    public function test_toData_withMinimalData_returnsArray(): void
    {
        // Arrange
        $paymentSession = new PaymentSession(
            order: 'https://api.example.com/orders/ord123'
        );

        // Act
        $array = $paymentSession->toData();

        // Assert
        $this->assertSame([
            'order' => 'https://api.example.com/orders/ord123',
        ], $array);
    }

    public function test_toData_withFullData_returnsArray(): void
    {
        // Arrange
        $paymentSession = new PaymentSession(
            order: 'https://api.example.com/orders/ord123',
            salesTax: Money::EUR(1250),
            shopperEmailAddress: 'test@example.com',
            returnUrl: 'https://merchant.com/return',
            customReference: 'TEST-REF',
        );

        // Act
        $array = $paymentSession->toData();

        // Assert
        $this->assertArrayHasKey('order', $array);
        $this->assertArrayHasKey('salesTax', $array);
        $this->assertSame('12.50', $array['salesTax']['amount']);
        $this->assertSame('test@example.com', $array['shopperEmailAddress']);
        $this->assertSame('https://merchant.com/return', $array['returnUrl']);
        $this->assertSame('TEST-REF', $array['customReference']);
    }

    public function test_toData_onlyIncludesNonNullValues(): void
    {
        // Arrange
        $paymentSession = new PaymentSession(
            order: 'https://api.example.com/orders/ord123',
            customReference: 'REF',
        );

        // Act
        $array = $paymentSession->toData();

        // Assert
        $this->assertArrayHasKey('order', $array);
        $this->assertArrayHasKey('customReference', $array);
        $this->assertArrayNotHasKey('id', $array);
        $this->assertArrayNotHasKey('salesTax', $array);
        $this->assertArrayNotHasKey('billTo', $array);
        $this->assertArrayNotHasKey('returnUrl', $array);
    }

    public function test_roundTrip_fromDataToData_preservesData(): void
    {
        // Arrange
        $originalData = [
            'order' => 'https://api.example.com/orders/ord123',
            'salesTax' => ['amount' => '8.99', 'currencyCode' => 'USD'],
            'shopperEmailAddress' => 'roundtrip@example.com',
            'returnUrl' => 'https://merchant.com/return',
            'customReference' => 'RT-123',
        ];

        // Act
        $paymentSession = PaymentSession::fromData($originalData);
        $resultData = $paymentSession->toData();

        // Assert
        $this->assertSame($originalData['order'], $resultData['order']);
        $this->assertSame($originalData['salesTax'], $resultData['salesTax']);
        $this->assertSame($originalData['shopperEmailAddress'], $resultData['shopperEmailAddress']);
        $this->assertSame($originalData['returnUrl'], $resultData['returnUrl']);
        $this->assertSame($originalData['customReference'], $resultData['customReference']);
    }

    public function test_properties_areReadonly(): void
    {
        // Arrange
        $paymentSession = new PaymentSession(
            order: 'https://api.example.com/orders/ord123'
        );

        // Act & Assert
        $reflection = new \ReflectionProperty($paymentSession, 'order');
        $this->assertTrue($reflection->isReadOnly());
    }
}
