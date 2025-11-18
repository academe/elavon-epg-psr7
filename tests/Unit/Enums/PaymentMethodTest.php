<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Enums;

use Academe\Elavon\Epg\Psr7\Enums\PaymentMethod;
use PHPUnit\Framework\TestCase;

/**
 * Tests for PaymentMethod enum.
 */
class PaymentMethodTest extends TestCase
{
    public function test_enumCases_containsAllExpectedMethods(): void
    {
        // Arrange & Act
        $cases = PaymentMethod::cases();

        // Assert
        $this->assertCount(3, $cases);
        $this->assertContains(PaymentMethod::CARD, $cases);
        $this->assertContains(PaymentMethod::BLIK, $cases);
        $this->assertContains(PaymentMethod::ACH, $cases);
    }

    public function test_enumValues_matchApiSpecification(): void
    {
        // Arrange & Act & Assert
        // Note: API uses PascalCase for Card, UPPERCASE for BLIK and ACH
        $this->assertSame('Card', PaymentMethod::CARD->value);
        $this->assertSame('BLIK', PaymentMethod::BLIK->value);
        $this->assertSame('ACH', PaymentMethod::ACH->value);
    }

    public function test_tryFrom_withValidValue_returnsCorrectCase(): void
    {
        // Arrange
        $value = 'Card';

        // Act
        $method = PaymentMethod::tryFrom($value);

        // Assert
        $this->assertSame(PaymentMethod::CARD, $method);
    }

    public function test_tryFrom_withInvalidValue_returnsNull(): void
    {
        // Arrange
        $value = 'invalid_method';

        // Act
        $method = PaymentMethod::tryFrom($value);

        // Assert
        $this->assertNull($method);
    }

    public function test_tryFrom_isCaseSensitive(): void
    {
        // Arrange & Act
        // 'card' (lowercase) should not match 'Card' (PascalCase)
        $result = PaymentMethod::tryFrom('card');

        // Assert
        $this->assertNull($result);
    }

    public function test_from_withValidValue_returnsCorrectCase(): void
    {
        // Arrange
        $value = 'BLIK';

        // Act
        $method = PaymentMethod::from($value);

        // Assert
        $this->assertSame(PaymentMethod::BLIK, $method);
    }

    public function test_from_withInvalidValue_throwsValueError(): void
    {
        // Arrange
        $value = 'nonexistent_method';

        // Assert
        $this->expectException(\ValueError::class);

        // Act
        PaymentMethod::from($value);
    }

    public function test_enumCases_areBackedByStrings(): void
    {
        // Arrange & Act
        $cases = PaymentMethod::cases();

        // Assert
        foreach ($cases as $case) {
            $this->assertIsString($case->value);
        }
    }
}
