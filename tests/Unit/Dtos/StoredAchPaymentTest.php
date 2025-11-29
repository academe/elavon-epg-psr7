<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\AchPayment;
use Academe\Elavon\Epg\Psr7\Dtos\StoredAchPayment;
use Academe\Elavon\Epg\Psr7\Enums\AchAccountType;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for StoredAchPayment DTO.
 */
class StoredAchPaymentTest extends TestCase
{
    public function test_construct_withAchPaymentObject_createsInstance(): void
    {
        // Arrange
        $achPayment = new AchPayment(
            achAccountType: AchAccountType::CHECKING_PERSONAL,
            accountName: 'John Doe',
            last4: '9999',
        );

        // Act
        $storedAchPayment = new StoredAchPayment(achPayment: $achPayment);

        // Assert
        $this->assertSame($achPayment, $storedAchPayment->achPayment);
    }

    public function test_construct_withAchPaymentArray_normalizesToAchPaymentObject(): void
    {
        // Arrange
        $achPaymentData = [
            'achAccountType' => 'checkingBusiness',
            'accountName' => 'Business Account',
            'last4' => '1234',
        ];

        // Act
        $storedAchPayment = StoredAchPayment::fromData(['achPayment' => $achPaymentData]);

        // Assert
        $this->assertInstanceOf(AchPayment::class, $storedAchPayment->achPayment);
        $this->assertSame(AchAccountType::CHECKING_BUSINESS, $storedAchPayment->achPayment->achAccountType);
        $this->assertSame('Business Account', $storedAchPayment->achPayment->accountName);
        $this->assertSame('1234', $storedAchPayment->achPayment->last4);
    }

    public function test_construct_withNullAchPayment_createsInstance(): void
    {
        // Act
        $storedAchPayment = StoredAchPayment::fromData(['achPayment' => null]);

        // Assert
        $this->assertNull($storedAchPayment->achPayment);
    }

    public function test_construct_withRequestFields_createsInstance(): void
    {
        // Act
        $storedAchPayment = StoredAchPayment::fromData([
            'shopper' => 'https://api.example.com/shoppers/s123',
            'hostedAchPayment' => 'https://api.example.com/hosted-ach-payments/hap456',
        ]);

        // Assert
        $this->assertSame('https://api.example.com/shoppers/s123', $storedAchPayment->shopper);
        $this->assertSame('https://api.example.com/hosted-ach-payments/hap456', $storedAchPayment->hostedAchPayment);
    }

    public function test_construct_withResponseFields_createsInstance(): void
    {
        // Act
        $storedAchPayment = StoredAchPayment::fromData([
            'href' => 'https://api.example.com/stored-ach-payments/sap123',
            'id' => 'sap123',
            'createdAt' => '2025-01-01T00:00:00Z',
            'modifiedAt' => '2025-01-02T00:00:00Z',
            'deletedAt' => '2025-01-31T23:59:59Z',
            'merchant' => 'https://api.example.com/merchants/m123',
        ]);

        // Assert
        $this->assertSame('https://api.example.com/stored-ach-payments/sap123', $storedAchPayment->href);
        $this->assertSame('sap123', $storedAchPayment->id);
        $this->assertSame('2025-01-01T00:00:00Z', $storedAchPayment->createdAt);
        $this->assertSame('2025-01-02T00:00:00Z', $storedAchPayment->modifiedAt);
        $this->assertSame('2025-01-31T23:59:59Z', $storedAchPayment->deletedAt);
        $this->assertSame('https://api.example.com/merchants/m123', $storedAchPayment->merchant);
    }

    public function test_construct_withCustomReferenceAndFields_createsInstance(): void
    {
        // Act
        $storedAchPayment = StoredAchPayment::fromData([
            'customReference' => 'ach-payment-789',
            'customFields' => ['customer_id' => 'cust123'],
        ]);

        // Assert
        $this->assertSame('ach-payment-789', $storedAchPayment->customReference);
        $this->assertSame(['customer_id' => 'cust123'], $storedAchPayment->customFields);
    }

    public function test_construct_withCustomReferenceTooLong_throwsException(): void
    {
        // Arrange
        $tooLongReference = str_repeat('a', 256);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Custom reference must not exceed 255 characters');

        // Act
        new StoredAchPayment(customReference: $tooLongReference);
    }

    public function test_fromData_withValidData_createsInstance(): void
    {
        // Arrange
        $data = [
            'href' => 'https://api.example.com/stored-ach-payments/sap456',
            'id' => 'sap456',
            'shopper' => 'https://api.example.com/shoppers/s456',
            'achPayment' => [
                'achAccountType' => 'savingsPersonal',
                'accountName' => 'Savings Account',
                'last4' => '5678',
            ],
            'customReference' => 'ref-999',
        ];

        // Act
        $storedAchPayment = StoredAchPayment::fromData($data);

        // Assert
        $this->assertInstanceOf(StoredAchPayment::class, $storedAchPayment);
        $this->assertSame('https://api.example.com/stored-ach-payments/sap456', $storedAchPayment->href);
        $this->assertSame('sap456', $storedAchPayment->id);
        $this->assertSame('https://api.example.com/shoppers/s456', $storedAchPayment->shopper);
        $this->assertInstanceOf(AchPayment::class, $storedAchPayment->achPayment);
        $this->assertSame(AchAccountType::SAVINGS_PERSONAL, $storedAchPayment->achPayment->achAccountType);
        $this->assertSame('ref-999', $storedAchPayment->customReference);
    }

    public function test_toData_withAllFields_serializesCorrectly(): void
    {
        // Arrange
        $achPayment = new AchPayment(
            achAccountType: AchAccountType::CHECKING_PERSONAL,
            accountName: 'Test Account',
            last4: '7890',
        );
        $storedAchPayment = new StoredAchPayment(
            achPayment: $achPayment,
            shopper: 'https://api.example.com/shoppers/s789',
            customReference: 'test-ref',
        );

        // Act
        $data = $storedAchPayment->toData();

        // Assert
        $this->assertIsArray($data);
        $this->assertArrayHasKey('achPayment', $data);
        $this->assertSame('https://api.example.com/shoppers/s789', $data['shopper']);
        $this->assertSame('test-ref', $data['customReference']);
        $this->assertSame('checkingPersonal', $data['achPayment']['achAccountType']);
    }
}
