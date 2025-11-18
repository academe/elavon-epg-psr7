<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Enums;

use Academe\Elavon\Epg\Psr7\Enums\TransactionType;
use PHPUnit\Framework\TestCase;

/**
 * Tests for TransactionType enum.
 */
class TransactionTypeTest extends TestCase
{
    public function test_enumCases_containsAllExpectedTypes(): void
    {
        // Arrange & Act
        $cases = TransactionType::cases();

        // Assert
        $this->assertCount(3, $cases);
        $this->assertContains(TransactionType::SALE, $cases);
        $this->assertContains(TransactionType::REFUND, $cases);
        $this->assertContains(TransactionType::VOID, $cases);
    }

    public function test_enumValues_matchApiSpecification(): void
    {
        // Arrange & Act & Assert
        $this->assertSame('sale', TransactionType::SALE->value);
        $this->assertSame('refund', TransactionType::REFUND->value);
        $this->assertSame('void', TransactionType::VOID->value);
    }

    public function test_tryFrom_withValidValue_returnsCorrectCase(): void
    {
        // Arrange
        $value = 'sale';

        // Act
        $type = TransactionType::tryFrom($value);

        // Assert
        $this->assertSame(TransactionType::SALE, $type);
    }

    public function test_tryFrom_withInvalidValue_returnsNull(): void
    {
        // Arrange
        $value = 'invalid_type';

        // Act
        $type = TransactionType::tryFrom($value);

        // Assert
        $this->assertNull($type);
    }

    public function test_from_withValidValue_returnsCorrectCase(): void
    {
        // Arrange
        $value = 'refund';

        // Act
        $type = TransactionType::from($value);

        // Assert
        $this->assertSame(TransactionType::REFUND, $type);
    }

    public function test_from_withInvalidValue_throwsValueError(): void
    {
        // Arrange
        $value = 'nonexistent_type';

        // Assert
        $this->expectException(\ValueError::class);

        // Act
        TransactionType::from($value);
    }

    public function test_enumCases_areBackedByStrings(): void
    {
        // Arrange & Act
        $cases = TransactionType::cases();

        // Assert
        foreach ($cases as $case) {
            $this->assertIsString($case->value);
        }
    }
}
