<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Enums;

use Academe\Elavon\Epg\Psr7\Enums\PaymentMethodQualifier;
use PHPUnit\Framework\TestCase;

/**
 * Tests for PaymentMethodQualifier enum.
 */
class PaymentMethodQualifierTest extends TestCase
{
    public function test_enumCases_containsAllExpectedQualifiers(): void
    {
        // Arrange & Act
        $cases = PaymentMethodQualifier::cases();

        // Assert
        $this->assertCount(2, $cases);
        $this->assertContains(PaymentMethodQualifier::CREDIT, $cases);
        $this->assertContains(PaymentMethodQualifier::DEBIT, $cases);
    }

    public function test_enumValues_matchApiSpecification(): void
    {
        // Arrange & Act & Assert
        $this->assertSame('credit', PaymentMethodQualifier::CREDIT->value);
        $this->assertSame('debit', PaymentMethodQualifier::DEBIT->value);
    }

    public function test_tryFrom_withValidValue_returnsCorrectCase(): void
    {
        // Arrange
        $value = 'credit';

        // Act
        $qualifier = PaymentMethodQualifier::tryFrom($value);

        // Assert
        $this->assertSame(PaymentMethodQualifier::CREDIT, $qualifier);
    }

    public function test_tryFrom_withInvalidValue_returnsNull(): void
    {
        // Arrange
        $value = 'invalid_qualifier';

        // Act
        $qualifier = PaymentMethodQualifier::tryFrom($value);

        // Assert
        $this->assertNull($qualifier);
    }

    public function test_from_withValidValue_returnsCorrectCase(): void
    {
        // Arrange
        $value = 'debit';

        // Act
        $qualifier = PaymentMethodQualifier::from($value);

        // Assert
        $this->assertSame(PaymentMethodQualifier::DEBIT, $qualifier);
    }

    public function test_from_withInvalidValue_throwsValueError(): void
    {
        // Arrange
        $value = 'nonexistent_qualifier';

        // Assert
        $this->expectException(\ValueError::class);

        // Act
        PaymentMethodQualifier::from($value);
    }

    public function test_enumCases_areBackedByStrings(): void
    {
        // Arrange & Act
        $cases = PaymentMethodQualifier::cases();

        // Assert
        foreach ($cases as $case) {
            $this->assertIsString($case->value);
        }
    }
}
