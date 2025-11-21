<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\AchPayment;
use Academe\Elavon\Epg\Psr7\Enums\AchAccountType;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for AchPayment DTO.
 */
class AchPaymentTest extends TestCase
{
    public function test_construct_withValidRequestData_createsInstance(): void
    {
        // Arrange & Act
        $achPayment = new AchPayment(
            achAccountType: AchAccountType::CHECKING_PERSONAL,
            accountName: 'John Doe',
            bankRoutingNumber: '123456789',
            bankAccountNumber: '1234567890',
        );

        // Assert
        $this->assertSame(AchAccountType::CHECKING_PERSONAL, $achPayment->achAccountType);
        $this->assertSame('John Doe', $achPayment->accountName);
        $this->assertSame('123456789', $achPayment->bankRoutingNumber);
        $this->assertSame('1234567890', $achPayment->bankAccountNumber);
        $this->assertNull($achPayment->bankAccountToken);
        $this->assertNull($achPayment->achFingerprint);
        $this->assertNull($achPayment->last4);
    }

    public function test_construct_withValidResponseData_createsInstance(): void
    {
        // Arrange & Act
        $achPayment = new AchPayment(
            achAccountType: AchAccountType::SAVINGS_PERSONAL,
            accountName: 'Jane Smith',
            achFingerprint: 'fingerprint123',
            last4: '1234',
        );

        // Assert
        $this->assertSame(AchAccountType::SAVINGS_PERSONAL, $achPayment->achAccountType);
        $this->assertSame('Jane Smith', $achPayment->accountName);
        $this->assertSame('fingerprint123', $achPayment->achFingerprint);
        $this->assertSame('1234', $achPayment->last4);
        $this->assertNull($achPayment->bankRoutingNumber);
        $this->assertNull($achPayment->bankAccountNumber);
        $this->assertNull($achPayment->bankAccountToken);
    }

    public function test_construct_withBankAccountToken_createsInstance(): void
    {
        // Arrange & Act
        $achPayment = new AchPayment(
            achAccountType: AchAccountType::CHECKING_BUSINESS,
            accountName: 'Business Account',
            bankAccountToken: 'token_abc123',
        );

        // Assert
        $this->assertSame(AchAccountType::CHECKING_BUSINESS, $achPayment->achAccountType);
        $this->assertSame('Business Account', $achPayment->accountName);
        $this->assertSame('token_abc123', $achPayment->bankAccountToken);
    }

    public function test_construct_withEmptyAccountName_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Account name cannot be empty');

        // Act
        new AchPayment(
            achAccountType: AchAccountType::CHECKING_PERSONAL,
            accountName: '',
        );
    }

    public function test_construct_withAccountNameTooLong_throwsException(): void
    {
        // Arrange
        $longName = str_repeat('a', 256);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Account name must not exceed 255 characters');

        // Act
        new AchPayment(
            achAccountType: AchAccountType::CHECKING_PERSONAL,
            accountName: $longName,
        );
    }

    public function test_construct_withAccountNameAt255Characters_isValid(): void
    {
        // Arrange
        $maxName = str_repeat('a', 255);

        // Act
        $achPayment = new AchPayment(
            achAccountType: AchAccountType::CHECKING_PERSONAL,
            accountName: $maxName,
        );

        // Assert
        $this->assertSame($maxName, $achPayment->accountName);
    }

    public function test_construct_withInvalidRoutingNumberTooShort_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Bank routing number must be exactly 9 digits');

        // Act
        new AchPayment(
            achAccountType: AchAccountType::CHECKING_PERSONAL,
            accountName: 'John Doe',
            bankRoutingNumber: '12345678',
        );
    }

    public function test_construct_withInvalidRoutingNumberTooLong_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Bank routing number must be exactly 9 digits');

        // Act
        new AchPayment(
            achAccountType: AchAccountType::CHECKING_PERSONAL,
            accountName: 'John Doe',
            bankRoutingNumber: '1234567890',
        );
    }

    public function test_construct_withInvalidRoutingNumberNonNumeric_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Bank routing number must be exactly 9 digits');

        // Act
        new AchPayment(
            achAccountType: AchAccountType::CHECKING_PERSONAL,
            accountName: 'John Doe',
            bankRoutingNumber: '12345678a',
        );
    }

    public function test_construct_withInvalidAccountNumberTooShort_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Bank account number must be 5 to 16 digits');

        // Act
        new AchPayment(
            achAccountType: AchAccountType::CHECKING_PERSONAL,
            accountName: 'John Doe',
            bankAccountNumber: '1234',
        );
    }

    public function test_construct_withInvalidAccountNumberTooLong_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Bank account number must be 5 to 16 digits');

        // Act
        new AchPayment(
            achAccountType: AchAccountType::CHECKING_PERSONAL,
            accountName: 'John Doe',
            bankAccountNumber: '12345678901234567',
        );
    }

    public function test_construct_withInvalidAccountNumberNonNumeric_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Bank account number must be 5 to 16 digits');

        // Act
        new AchPayment(
            achAccountType: AchAccountType::CHECKING_PERSONAL,
            accountName: 'John Doe',
            bankAccountNumber: '123456abc',
        );
    }

    public function test_construct_withAccountNumberAt5Digits_isValid(): void
    {
        // Act
        $achPayment = new AchPayment(
            achAccountType: AchAccountType::CHECKING_PERSONAL,
            accountName: 'John Doe',
            bankAccountNumber: '12345',
        );

        // Assert
        $this->assertSame('12345', $achPayment->bankAccountNumber);
    }

    public function test_construct_withAccountNumberAt16Digits_isValid(): void
    {
        // Act
        $achPayment = new AchPayment(
            achAccountType: AchAccountType::CHECKING_PERSONAL,
            accountName: 'John Doe',
            bankAccountNumber: '1234567890123456',
        );

        // Assert
        $this->assertSame('1234567890123456', $achPayment->bankAccountNumber);
    }

    public function test_construct_withInvalidLast4TooShort_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Last 4 digits must be exactly 4 digits');

        // Act
        new AchPayment(
            achAccountType: AchAccountType::CHECKING_PERSONAL,
            accountName: 'John Doe',
            last4: '123',
        );
    }

    public function test_construct_withInvalidLast4TooLong_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Last 4 digits must be exactly 4 digits');

        // Act
        new AchPayment(
            achAccountType: AchAccountType::CHECKING_PERSONAL,
            accountName: 'John Doe',
            last4: '12345',
        );
    }

    public function test_construct_withInvalidLast4NonNumeric_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Last 4 digits must be exactly 4 digits');

        // Act
        new AchPayment(
            achAccountType: AchAccountType::CHECKING_PERSONAL,
            accountName: 'John Doe',
            last4: '123a',
        );
    }

    public function test_fromData_withRequestData_createsInstance(): void
    {
        // Arrange
        $data = [
            'achAccountType' => 'checkingPersonal',
            'accountName' => 'John Doe',
            'bankRoutingNumber' => '123456789',
            'bankAccountNumber' => '1234567890',
        ];

        // Act
        $achPayment = AchPayment::fromData($data);

        // Assert
        $this->assertSame(AchAccountType::CHECKING_PERSONAL, $achPayment->achAccountType);
        $this->assertSame('John Doe', $achPayment->accountName);
        $this->assertSame('123456789', $achPayment->bankRoutingNumber);
        $this->assertSame('1234567890', $achPayment->bankAccountNumber);
    }

    public function test_fromData_withResponseData_createsInstance(): void
    {
        // Arrange
        $data = [
            'achAccountType' => 'savingsPersonal',
            'accountName' => 'Jane Smith',
            'achFingerprint' => 'fingerprint123',
            'last4' => '1234',
        ];

        // Act
        $achPayment = AchPayment::fromData($data);

        // Assert
        $this->assertSame(AchAccountType::SAVINGS_PERSONAL, $achPayment->achAccountType);
        $this->assertSame('Jane Smith', $achPayment->accountName);
        $this->assertSame('fingerprint123', $achPayment->achFingerprint);
        $this->assertSame('1234', $achPayment->last4);
    }

    public function test_toData_withRequestData_returnsArray(): void
    {
        // Arrange
        $achPayment = new AchPayment(
            achAccountType: AchAccountType::CHECKING_BUSINESS,
            accountName: 'Business Account',
            bankRoutingNumber: '987654321',
            bankAccountNumber: '9876543210',
        );

        // Act
        $array = $achPayment->toData();

        // Assert
        $this->assertSame([
            'achAccountType' => 'checkingBusiness',
            'bankRoutingNumber' => '987654321',
            'bankAccountNumber' => '9876543210',
            'accountName' => 'Business Account',
        ], $array);
    }

    public function test_toData_withResponseData_returnsArray(): void
    {
        // Arrange
        $achPayment = new AchPayment(
            achAccountType: AchAccountType::SAVINGS_PERSONAL,
            accountName: 'Jane Smith',
            achFingerprint: 'fingerprint456',
            last4: '5678',
        );

        // Act
        $array = $achPayment->toData();

        // Assert
        $this->assertSame([
            'achAccountType' => 'savingsPersonal',
            'achFingerprint' => 'fingerprint456',
            'last4' => '5678',
            'accountName' => 'Jane Smith',
        ], $array);
    }

    public function test_toData_onlyIncludesNonNullValues(): void
    {
        // Arrange
        $achPayment = new AchPayment(
            achAccountType: AchAccountType::CHECKING_PERSONAL,
            accountName: 'John Doe',
        );

        // Act
        $array = $achPayment->toData();

        // Assert
        $this->assertSame([
            'achAccountType' => 'checkingPersonal',
            'accountName' => 'John Doe',
        ], $array);
        $this->assertArrayNotHasKey('bankRoutingNumber', $array);
        $this->assertArrayNotHasKey('bankAccountNumber', $array);
        $this->assertArrayNotHasKey('bankAccountToken', $array);
        $this->assertArrayNotHasKey('achFingerprint', $array);
        $this->assertArrayNotHasKey('last4', $array);
    }

    public function test_roundTrip_fromDataToData_preservesData(): void
    {
        // Arrange
        $originalData = [
            'achAccountType' => 'checkingPersonal',
            'bankRoutingNumber' => '123456789',
            'bankAccountNumber' => '1234567890',
            'bankAccountToken' => 'token_abc',
            'accountName' => 'John Doe',
        ];

        // Act
        $achPayment = AchPayment::fromData($originalData);
        $resultData = $achPayment->toData();

        // Assert
        $this->assertSame($originalData, $resultData);
    }

    public function test_properties_areReadonly(): void
    {
        // Arrange
        $achPayment = new AchPayment(
            achAccountType: AchAccountType::CHECKING_PERSONAL,
            accountName: 'John Doe',
        );

        // Act & Assert
        $reflection = new \ReflectionProperty($achPayment, 'accountName');
        $this->assertTrue($reflection->isReadOnly());
    }
}
