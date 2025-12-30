<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\DebtorAccount;
use Academe\Elavon\Epg\Psr7\Dtos\PaymentLink;
use Academe\Elavon\Epg\Psr7\Enums\PaymentLinkStatus;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\ValueObjects\CustomFields;
use DateTimeImmutable;
use Money\Money;
use PHPUnit\Framework\TestCase;

/**
 * Tests for PaymentLink DTO.
 */
class PaymentLinkTest extends TestCase
{
    public function test_construct_withMinimalFields_createsInstance(): void
    {
        // Arrange & Act
        $paymentLink = PaymentLink::fromData([
            'total' => ['amount' => '100.00', 'currencyCode' => 'USD'],
            'expiresAt' => '2025-12-31T23:59:59Z',
        ]);

        // Assert
        $this->assertInstanceOf(Money::class, $paymentLink->total);
        $this->assertSame('10000', $paymentLink->total->getAmount());
        $this->assertSame('USD', $paymentLink->total->getCurrency()->getCode());
        $this->assertInstanceOf(DateTimeImmutable::class, $paymentLink->expiresAt);
        $this->assertSame('2025-12-31 23:59:59', $paymentLink->expiresAt->format('Y-m-d H:i:s'));
        $this->assertNull($paymentLink->id);
        $this->assertNull($paymentLink->description);
    }

    public function test_construct_withAllFields_createsInstance(): void
    {
        // Arrange & Act
        $paymentLink = PaymentLink::fromData([
            'href' => 'https://api.example.com/payment-links/pl123',
            'id' => 'pl123',
            'merchant' => 'https://api.example.com/merchants/m123',
            'account' => 'https://api.example.com/accounts/a123',
            'url' => 'https://hpp.example.com/payment-links/pl123',
            'returnUrl' => 'https://merchant.com/return',
            'createdAt' => '2025-11-19T10:00:00Z',
            'createdBy' => 'user@example.com',
            'modifiedAt' => '2025-11-19T11:00:00Z',
            'expiresAt' => '2025-12-31T23:59:59Z',
            'cancelledAt' => null,
            'cancelledBy' => null,
            'doCancel' => false,
            'doCapture' => true,
            'conversionCount' => 5,
            'conversionLimit' => 10,
            'description' => 'Payment for Invoice #12345',
            'total' => ['amount' => '250.00', 'currencyCode' => 'USD'],
            'salesTax' => ['amount' => '25.00', 'currencyCode' => 'USD'],
            'debtorAccount' => ['lastName' => 'Smith', 'postalCode' => 'SW1A 1AA'],
            'orderReference' => 'ORD-67890',
            'shopperEmailAddress' => 'shopper@example.com',
            'shopper' => 'https://api.example.com/shoppers/s123',
            'status' => ['active'],
            'useStoredPaymentMethod' => true,
            'customReference' => 'CUST-REF-111',
            'customFields' => ['field1' => 'value1'],
        ]);

        // Assert
        $this->assertSame('https://api.example.com/payment-links/pl123', $paymentLink->href);
        $this->assertSame('pl123', $paymentLink->id);
        $this->assertSame('https://api.example.com/merchants/m123', $paymentLink->merchant);
        $this->assertSame('https://api.example.com/accounts/a123', $paymentLink->account);
        $this->assertSame('https://hpp.example.com/payment-links/pl123', $paymentLink->url);
        $this->assertSame('https://merchant.com/return', $paymentLink->returnUrl);
        $this->assertInstanceOf(DateTimeImmutable::class, $paymentLink->createdAt);
        $this->assertSame('2025-11-19 10:00:00', $paymentLink->createdAt->format('Y-m-d H:i:s'));
        $this->assertSame('user@example.com', $paymentLink->createdBy);
        $this->assertInstanceOf(DateTimeImmutable::class, $paymentLink->modifiedAt);
        $this->assertSame('2025-11-19 11:00:00', $paymentLink->modifiedAt->format('Y-m-d H:i:s'));
        $this->assertInstanceOf(DateTimeImmutable::class, $paymentLink->expiresAt);
        $this->assertSame('2025-12-31 23:59:59', $paymentLink->expiresAt->format('Y-m-d H:i:s'));
        $this->assertFalse($paymentLink->doCancel);
        $this->assertTrue($paymentLink->doCapture);
        $this->assertSame(5, $paymentLink->conversionCount);
        $this->assertSame(10, $paymentLink->conversionLimit);
        $this->assertSame('Payment for Invoice #12345', $paymentLink->description);
        $this->assertSame('25000', $paymentLink->total->getAmount());
        $this->assertInstanceOf(Money::class, $paymentLink->salesTax);
        $this->assertInstanceOf(DebtorAccount::class, $paymentLink->debtorAccount);
        $this->assertSame('ORD-67890', $paymentLink->orderReference);
        $this->assertSame('shopper@example.com', $paymentLink->shopperEmailAddress);
        $this->assertSame('https://api.example.com/shoppers/s123', $paymentLink->shopper);
        $this->assertSame([PaymentLinkStatus::ACTIVE], $paymentLink->status);
        $this->assertTrue($paymentLink->useStoredPaymentMethod);
        $this->assertSame('CUST-REF-111', $paymentLink->customReference);
        $this->assertSame(['field1' => 'value1'], $paymentLink->customFields->all());
    }

    public function test_construct_withMoneyObject_createsInstance(): void
    {
        // Arrange
        $money = Money::EUR(15000); // 150.00 EUR
        $expiresAt = new DateTimeImmutable('2025-12-31T23:59:59Z');

        // Act
        $paymentLink = new PaymentLink(
            total: $money,
            expiresAt: $expiresAt,
        );

        // Assert
        $this->assertSame($money, $paymentLink->total);
    }

    public function test_construct_withDebtorAccountObject_createsInstance(): void
    {
        // Arrange
        $debtorAccount = new DebtorAccount(
            lastName: 'Smith',
            postalCode: 'SW1A 1AA',
        );
        $expiresAt = new DateTimeImmutable('2025-12-31T23:59:59Z');

        // Act
        $paymentLink = new PaymentLink(
            total: Money::USD(7500),
            expiresAt: $expiresAt,
            debtorAccount: $debtorAccount,
        );

        // Assert
        $this->assertSame($debtorAccount, $paymentLink->debtorAccount);
    }

    public function test_construct_withInvalidReturnUrl_throwsException(): void
    {
        // Arrange
        $longUrl = str_repeat('a', 2049);
        $expiresAt = new DateTimeImmutable('2025-12-31T23:59:59Z');

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Return URL must not exceed 2048 characters');

        // Act
        new PaymentLink(
            total: Money::USD(1000),
            expiresAt: $expiresAt,
            returnUrl: $longUrl,
        );
    }

    public function test_construct_withInvalidReturnUrlPattern_throwsException(): void
    {
        // Arrange
        $expiresAt = new DateTimeImmutable('2025-12-31T23:59:59Z');

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Return URL must be a valid HTTP/HTTPS URL');

        // Act
        new PaymentLink(
            total: Money::USD(1000),
            expiresAt: $expiresAt,
            returnUrl: 'ftp://invalid.com',
        );
    }

    public function test_construct_withTooLongDescription_throwsException(): void
    {
        // Arrange
        $longDescription = str_repeat('a', 256);
        $expiresAt = new DateTimeImmutable('2025-12-31T23:59:59Z');

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Description must not exceed 255 characters');

        // Act
        new PaymentLink(
            total: Money::USD(1000),
            expiresAt: $expiresAt,
            description: $longDescription,
        );
    }

    public function test_construct_withTooLongShopperEmail_throwsException(): void
    {
        // Arrange
        $longEmail = str_repeat('a', 255) . '@example.com';
        $expiresAt = new DateTimeImmutable('2025-12-31T23:59:59Z');

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Shopper email address must not exceed 254 characters');

        // Act
        new PaymentLink(
            total: Money::USD(1000),
            expiresAt: $expiresAt,
            shopperEmailAddress: $longEmail,
        );
    }

    public function test_construct_withInvalidStatus_throwsException(): void
    {
        // Arrange
        $expiresAt = new DateTimeImmutable('2025-12-31T23:59:59Z');

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Status array must contain PaymentLinkStatus enum values');

        // Act - passing a string instead of PaymentLinkStatus enum
        new PaymentLink(
            total: Money::USD(1000),
            expiresAt: $expiresAt,
            status: ['invalid_status'],
        );
    }

    public function test_fromData_withMinimalData_createsInstance(): void
    {
        // Arrange
        $data = [
            'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
            'expiresAt' => '2025-12-31T23:59:59Z',
        ];

        // Act
        $paymentLink = PaymentLink::fromData($data);

        // Assert
        $this->assertSame('9999', $paymentLink->total->getAmount());
        $this->assertInstanceOf(DateTimeImmutable::class, $paymentLink->expiresAt);
        $this->assertSame('2025-12-31 23:59:59', $paymentLink->expiresAt->format('Y-m-d H:i:s'));
        $this->assertNull($paymentLink->id);
    }

    public function test_fromData_withFullData_createsInstance(): void
    {
        // Arrange
        $data = [
            'href' => 'https://api.example.com/payment-links/pl123',
            'id' => 'pl123',
            'merchant' => 'https://api.example.com/merchants/m123',
            'total' => ['amount' => '500.00', 'currencyCode' => 'GBP'],
            'expiresAt' => '2025-12-31T23:59:59Z',
            'description' => 'Payment Link for Invoice',
            'returnUrl' => 'https://merchant.com/return',
            'shopperEmailAddress' => 'alice@example.com',
            'orderReference' => 'ORD-888',
            'customReference' => 'CUSTOM-777',
            'customFields' => ['project' => 'Alpha'],
            'status' => ['active', 'completed'],
        ];

        // Act
        $paymentLink = PaymentLink::fromData($data);

        // Assert
        $this->assertSame('pl123', $paymentLink->id);
        $this->assertSame('50000', $paymentLink->total->getAmount());
        $this->assertSame('Payment Link for Invoice', $paymentLink->description);
        $this->assertSame('alice@example.com', $paymentLink->shopperEmailAddress);
        $this->assertSame([PaymentLinkStatus::ACTIVE, PaymentLinkStatus::COMPLETED], $paymentLink->status);
    }

    public function test_toData_withMinimalData_returnsArray(): void
    {
        // Arrange
        $paymentLink = PaymentLink::fromData([
            'total' => ['amount' => '50.00', 'currencyCode' => 'USD'],
            'expiresAt' => '2025-12-31T23:59:59Z',
        ]);

        // Act
        $array = $paymentLink->toData();

        // Assert
        $this->assertEquals([
            'total' => [
                'amount' => '50.00',
                'currencyCode' => 'USD',
            ],
            'expiresAt' => '2025-12-31T23:59:59.000+00:00',
        ], $array);
    }

    public function test_toData_onlyIncludesNonNullValues(): void
    {
        // Arrange
        $paymentLink = PaymentLink::fromData([
            'total' => ['amount' => '25.00', 'currencyCode' => 'USD'],
            'expiresAt' => '2025-12-31T23:59:59Z',
            'description' => 'Test payment link',
        ]);

        // Act
        $array = $paymentLink->toData();

        // Assert
        $this->assertArrayHasKey('total', $array);
        $this->assertArrayHasKey('expiresAt', $array);
        $this->assertArrayHasKey('description', $array);
        $this->assertArrayNotHasKey('id', $array);
        $this->assertArrayNotHasKey('href', $array);
        $this->assertArrayNotHasKey('url', $array);
    }

    public function test_roundTrip_fromDataToData_preservesData(): void
    {
        // Arrange
        $originalData = [
            'total' => ['amount' => '150.00', 'currencyCode' => 'USD'],
            'expiresAt' => '2025-12-31T23:59:59Z',
            'description' => 'Test payment link',
            'returnUrl' => 'https://merchant.com/return',
            'shopperEmailAddress' => 'test@example.com',
        ];

        // Act
        $paymentLink = PaymentLink::fromData($originalData);
        $resultData = $paymentLink->toData();

        // Assert
        $this->assertSame($originalData['total'], $resultData['total']);
        // expiresAt is serialized in RFC3339_EXTENDED format
        $this->assertSame('2025-12-31T23:59:59.000+00:00', $resultData['expiresAt']);
        $this->assertSame($originalData['description'], $resultData['description']);
        $this->assertSame($originalData['returnUrl'], $resultData['returnUrl']);
        $this->assertSame($originalData['shopperEmailAddress'], $resultData['shopperEmailAddress']);
    }
}
