<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\AchPayment;
use Academe\Elavon\Epg\Psr7\Dtos\HostedAchPayment;
use Academe\Elavon\Epg\Psr7\Enums\AchAccountType;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for HostedAchPayment DTO.
 */
class HostedAchPaymentTest extends TestCase
{
    public function test_construct_withAchPaymentObject_createsInstance(): void
    {
        // Arrange
        $achPayment = new AchPayment(
            achAccountType: AchAccountType::CHECKING_BUSINESS,
            accountName: 'Business Account',
            bankRoutingNumber: '123456789',
            bankAccountNumber: '9876543210',
        );

        // Act
        $hostedAchPayment = new HostedAchPayment(achPayment: $achPayment);

        // Assert
        $this->assertSame($achPayment, $hostedAchPayment->achPayment);
    }

    public function test_construct_withAchPaymentArray_normalizesToAchPaymentObject(): void
    {
        // Arrange
        $achPaymentData = [
            'achAccountType' => 'checkingPersonal',
            'accountName' => 'Personal Checking',
            'bankRoutingNumber' => '987654321',
            'bankAccountNumber' => '1234567890',
        ];

        // Act
        $hostedAchPayment = HostedAchPayment::fromData(['achPayment' => $achPaymentData]);

        // Assert
        $this->assertInstanceOf(AchPayment::class, $hostedAchPayment->achPayment);
        $this->assertSame(AchAccountType::CHECKING_PERSONAL, $hostedAchPayment->achPayment->achAccountType);
        $this->assertSame('Personal Checking', $hostedAchPayment->achPayment->accountName);
        $this->assertSame('987654321', $hostedAchPayment->achPayment->bankRoutingNumber);
    }

    public function test_construct_withNullAchPayment_createsInstance(): void
    {
        // Act
        $hostedAchPayment = new HostedAchPayment(achPayment: null);

        // Assert
        $this->assertNull($hostedAchPayment->achPayment);
    }

    public function test_construct_withResponseFields_createsInstance(): void
    {
        // Act
        $hostedAchPayment = new HostedAchPayment(
            href: 'https://api.example.com/hosted-ach-payments/hap123',
            id: 'hap123',
            createdAt: '2025-01-01T00:00:00Z',
            modifiedAt: '2025-01-02T00:00:00Z',
            expiresAt: '2025-01-01T00:10:00Z',
            merchant: 'https://api.example.com/merchants/m123',
        );

        // Assert
        $this->assertSame('https://api.example.com/hosted-ach-payments/hap123', $hostedAchPayment->href);
        $this->assertSame('hap123', $hostedAchPayment->id);
        $this->assertSame('2025-01-01T00:00:00Z', $hostedAchPayment->createdAt);
        $this->assertSame('2025-01-02T00:00:00Z', $hostedAchPayment->modifiedAt);
        $this->assertSame('2025-01-01T00:10:00Z', $hostedAchPayment->expiresAt);
        $this->assertSame('https://api.example.com/merchants/m123', $hostedAchPayment->merchant);
    }

    public function test_construct_withCustomReferenceAndFields_createsInstance(): void
    {
        // Act
        $hostedAchPayment = new HostedAchPayment(
            customReference: 'hosted-ach-789',
            customFields: ['session_id' => 'sess123'],
        );

        // Assert
        $this->assertSame('hosted-ach-789', $hostedAchPayment->customReference);
        $this->assertSame(['session_id' => 'sess123'], $hostedAchPayment->customFields);
    }

    public function test_construct_withCustomReferenceTooLong_throwsException(): void
    {
        // Arrange
        $tooLongReference = str_repeat('a', 256);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Custom reference must not exceed 255 characters');

        // Act
        new HostedAchPayment(customReference: $tooLongReference);
    }

    public function test_fromData_withValidData_createsInstance(): void
    {
        // Arrange
        $data = [
            'href' => 'https://api.example.com/hosted-ach-payments/hap456',
            'id' => 'hap456',
            'expiresAt' => '2025-01-15T12:10:00Z',
            'achPayment' => [
                'achAccountType' => 'savingsPersonal',
                'accountName' => 'Savings',
                'last4' => '4321',
            ],
            'customReference' => 'ref-555',
        ];

        // Act
        $hostedAchPayment = HostedAchPayment::fromData($data);

        // Assert
        $this->assertInstanceOf(HostedAchPayment::class, $hostedAchPayment);
        $this->assertSame('https://api.example.com/hosted-ach-payments/hap456', $hostedAchPayment->href);
        $this->assertSame('hap456', $hostedAchPayment->id);
        $this->assertSame('2025-01-15T12:10:00Z', $hostedAchPayment->expiresAt);
        $this->assertInstanceOf(AchPayment::class, $hostedAchPayment->achPayment);
        $this->assertSame('ref-555', $hostedAchPayment->customReference);
    }

    public function test_toData_withAllFields_serializesCorrectly(): void
    {
        // Arrange
        $achPayment = new AchPayment(
            achAccountType: AchAccountType::CHECKING_BUSINESS,
            accountName: 'Corp Account',
            bankRoutingNumber: '111222333',
            bankAccountNumber: '44455566667777',
        );
        $hostedAchPayment = new HostedAchPayment(
            achPayment: $achPayment,
            customReference: 'test-ref',
        );

        // Act
        $data = $hostedAchPayment->toData();

        // Assert
        $this->assertIsArray($data);
        $this->assertArrayHasKey('achPayment', $data);
        $this->assertSame('test-ref', $data['customReference']);
        $this->assertSame('checkingBusiness', $data['achPayment']['achAccountType']);
        $this->assertSame('111222333', $data['achPayment']['bankRoutingNumber']);
    }
}
