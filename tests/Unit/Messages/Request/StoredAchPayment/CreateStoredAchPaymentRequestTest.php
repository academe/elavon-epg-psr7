<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\StoredAchPayment;

use Academe\Elavon\Epg\Psr7\Dtos\AchPayment;
use Academe\Elavon\Epg\Psr7\Dtos\StoredAchPayment;
use Academe\Elavon\Epg\Psr7\Enums\AchAccountType;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\StoredAchPayment\CreateStoredAchPaymentRequest;
use PHPUnit\Framework\TestCase;

/**
 * Tests for CreateStoredAchPaymentRequest.
 */
class CreateStoredAchPaymentRequestTest extends TestCase
{
    public function test_construct_withValidData_createsInstance(): void
    {
        // Arrange
        $achPayment = new AchPayment(
            achAccountType: AchAccountType::CHECKING_PERSONAL,
            accountName: 'John Doe',
            bankRoutingNumber: '123456789',
            bankAccountNumber: '9876543210',
        );
        $storedAchPayment = new StoredAchPayment(
            achPayment: $achPayment,
            shopper: 'https://api.example.com/shoppers/s123',
        );

        // Act
        $request = new CreateStoredAchPaymentRequest($storedAchPayment);

        // Assert
        $this->assertInstanceOf(CreateStoredAchPaymentRequest::class, $request);
        $this->assertSame($storedAchPayment, $request->storedAchPayment);
    }

    public function test_construct_withoutShopper_throwsException(): void
    {
        // Arrange
        $storedAchPayment = new StoredAchPayment();

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Shopper URL is required');

        // Act
        new CreateStoredAchPaymentRequest($storedAchPayment);
    }

    public function test_construct_withoutAchPaymentOrHosted_throwsException(): void
    {
        // Arrange
        $storedAchPayment = new StoredAchPayment(
            shopper: 'https://api.example.com/shoppers/s123',
        );

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Either hostedAchPayment URL or achPayment data is required');

        // Act
        new CreateStoredAchPaymentRequest($storedAchPayment);
    }

    public function test_build_returnsValidPsr7Request(): void
    {
        // Arrange
        $storedAchPayment = new StoredAchPayment(
            shopper: 'https://api.example.com/shoppers/s123',
            hostedAchPayment: 'https://api.example.com/hosted-ach-payments/hap456',
        );
        $createRequest = new CreateStoredAchPaymentRequest($storedAchPayment);

        // Act
        $psrRequest = $createRequest->build();

        // Assert
        $this->assertSame('POST', $psrRequest->getMethod());
        $this->assertStringContainsString('/stored-ach-payments', (string) $psrRequest->getUri());
    }
}
