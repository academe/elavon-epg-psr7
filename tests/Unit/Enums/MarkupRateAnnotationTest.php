<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Enums;

use Academe\Elavon\Epg\Psr7\Enums\MarkupRateAnnotation;
use PHPUnit\Framework\TestCase;

/**
 * Tests for MarkupRateAnnotation enum.
 */
class MarkupRateAnnotationTest extends TestCase
{
    public function test_enumCases_containsAllExpectedAnnotations(): void
    {
        // Arrange & Act
        $cases = MarkupRateAnnotation::cases();

        // Assert
        $this->assertCount(3, $cases);
        $this->assertContains(MarkupRateAnnotation::NONE, $cases);
        $this->assertContains(MarkupRateAnnotation::ABOVE_ECB, $cases);
        $this->assertContains(MarkupRateAnnotation::BELOW_ECB, $cases);
    }

    public function test_enumValues_matchApiSpecification(): void
    {
        // Arrange & Act & Assert
        $this->assertSame('none', MarkupRateAnnotation::NONE->value);
        $this->assertSame('aboveEcb', MarkupRateAnnotation::ABOVE_ECB->value);
        $this->assertSame('belowEcb', MarkupRateAnnotation::BELOW_ECB->value);
    }

    public function test_tryFrom_withValidValue_returnsCorrectCase(): void
    {
        // Arrange
        $value = 'none';

        // Act
        $annotation = MarkupRateAnnotation::tryFrom($value);

        // Assert
        $this->assertSame(MarkupRateAnnotation::NONE, $annotation);
    }

    public function test_tryFrom_withInvalidValue_returnsNull(): void
    {
        // Arrange
        $value = 'invalid_annotation';

        // Act
        $annotation = MarkupRateAnnotation::tryFrom($value);

        // Assert
        $this->assertNull($annotation);
    }

    public function test_from_withValidValue_returnsCorrectCase(): void
    {
        // Arrange
        $value = 'aboveEcb';

        // Act
        $annotation = MarkupRateAnnotation::from($value);

        // Assert
        $this->assertSame(MarkupRateAnnotation::ABOVE_ECB, $annotation);
    }

    public function test_from_withInvalidValue_throwsValueError(): void
    {
        // Arrange
        $value = 'nonexistent_annotation';

        // Assert
        $this->expectException(\ValueError::class);

        // Act
        MarkupRateAnnotation::from($value);
    }

    public function test_enumCases_areBackedByStrings(): void
    {
        // Arrange & Act
        $cases = MarkupRateAnnotation::cases();

        // Assert
        foreach ($cases as $case) {
            $this->assertIsString($case->value);
        }
    }
}
