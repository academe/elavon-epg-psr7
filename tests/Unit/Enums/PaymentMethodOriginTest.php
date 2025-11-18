<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Enums;

use Academe\Elavon\Epg\Psr7\Enums\PaymentMethodOrigin;
use PHPUnit\Framework\TestCase;

/**
 * Tests for PaymentMethodOrigin enum.
 */
class PaymentMethodOriginTest extends TestCase
{
    public function test_enumCases_containsAllExpectedOrigins(): void
    {
        // Arrange & Act
        $cases = PaymentMethodOrigin::cases();

        // Assert
        $this->assertCount(8, $cases);
        $this->assertContains(PaymentMethodOrigin::CARD, $cases);
        $this->assertContains(PaymentMethodOrigin::APPLE_PAY, $cases);
        $this->assertContains(PaymentMethodOrigin::GOOGLE_PAY, $cases);
        $this->assertContains(PaymentMethodOrigin::PAZE, $cases);
        $this->assertContains(PaymentMethodOrigin::BLIK, $cases);
        $this->assertContains(PaymentMethodOrigin::POLISH_BANK_TRANSFER, $cases);
        $this->assertContains(PaymentMethodOrigin::ACH, $cases);
        $this->assertContains(PaymentMethodOrigin::UNKNOWN_WALLET, $cases);
    }

    public function test_enumValues_matchApiSpecification(): void
    {
        // Arrange & Act & Assert
        // Note: API uses mixed case and spaces - exact values required
        $this->assertSame('Card', PaymentMethodOrigin::CARD->value);
        $this->assertSame('Apple Pay', PaymentMethodOrigin::APPLE_PAY->value);
        $this->assertSame('Google Pay', PaymentMethodOrigin::GOOGLE_PAY->value);
        $this->assertSame('Paze', PaymentMethodOrigin::PAZE->value);
        $this->assertSame('BLIK', PaymentMethodOrigin::BLIK->value);
        $this->assertSame('Polish Bank Transfer', PaymentMethodOrigin::POLISH_BANK_TRANSFER->value);
        $this->assertSame('ACH', PaymentMethodOrigin::ACH->value);
        $this->assertSame('Unknown Wallet', PaymentMethodOrigin::UNKNOWN_WALLET->value);
    }

    public function test_tryFrom_withValidValue_returnsCorrectCase(): void
    {
        // Arrange
        $value = 'Apple Pay';

        // Act
        $origin = PaymentMethodOrigin::tryFrom($value);

        // Assert
        $this->assertSame(PaymentMethodOrigin::APPLE_PAY, $origin);
    }

    public function test_tryFrom_withInvalidValue_returnsNull(): void
    {
        // Arrange
        $value = 'invalid_origin';

        // Act
        $origin = PaymentMethodOrigin::tryFrom($value);

        // Assert
        $this->assertNull($origin);
    }

    public function test_tryFrom_isCaseSensitive(): void
    {
        // Arrange & Act
        // 'card' (lowercase) should not match 'Card' (PascalCase)
        $result = PaymentMethodOrigin::tryFrom('card');

        // Assert
        $this->assertNull($result);
    }

    public function test_tryFrom_requiresExactSpacing(): void
    {
        // Arrange & Act
        // 'ApplePay' (no space) should not match 'Apple Pay' (with space)
        $result = PaymentMethodOrigin::tryFrom('ApplePay');

        // Assert
        $this->assertNull($result);
    }

    public function test_from_withValidValue_returnsCorrectCase(): void
    {
        // Arrange
        $value = 'Google Pay';

        // Act
        $origin = PaymentMethodOrigin::from($value);

        // Assert
        $this->assertSame(PaymentMethodOrigin::GOOGLE_PAY, $origin);
    }

    public function test_from_withInvalidValue_throwsValueError(): void
    {
        // Arrange
        $value = 'nonexistent_origin';

        // Assert
        $this->expectException(\ValueError::class);

        // Act
        PaymentMethodOrigin::from($value);
    }

    public function test_enumCases_areBackedByStrings(): void
    {
        // Arrange & Act
        $cases = PaymentMethodOrigin::cases();

        // Assert
        foreach ($cases as $case) {
            $this->assertIsString($case->value);
        }
    }
}
