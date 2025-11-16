<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Enums;

use Academe\Elavon\Epg\Psr7\Enums\ProcessorDirective;
use PHPUnit\Framework\TestCase;

/**
 * Tests for ProcessorDirective enum.
 */
class ProcessorDirectiveTest extends TestCase
{
    public function test_enumCases_containsAllExpectedDirectives(): void
    {
        // Arrange & Act
        $cases = ProcessorDirective::cases();

        // Assert
        $this->assertCount(2, $cases);
        $this->assertContains(ProcessorDirective::NONE, $cases);
        $this->assertContains(ProcessorDirective::REVERSAL, $cases);
    }

    public function test_enumValues_matchApiSpecification(): void
    {
        // Arrange & Act & Assert
        $this->assertSame('none', ProcessorDirective::NONE->value);
        $this->assertSame('reversal', ProcessorDirective::REVERSAL->value);
    }

    public function test_tryFrom_withValidValue_returnsCorrectCase(): void
    {
        // Arrange
        $value = 'none';

        // Act
        $directive = ProcessorDirective::tryFrom($value);

        // Assert
        $this->assertSame(ProcessorDirective::NONE, $directive);
    }

    public function test_tryFrom_withInvalidValue_returnsNull(): void
    {
        // Arrange
        $value = 'invalid_directive';

        // Act
        $directive = ProcessorDirective::tryFrom($value);

        // Assert
        $this->assertNull($directive);
    }

    public function test_from_withValidValue_returnsCorrectCase(): void
    {
        // Arrange
        $value = 'reversal';

        // Act
        $directive = ProcessorDirective::from($value);

        // Assert
        $this->assertSame(ProcessorDirective::REVERSAL, $directive);
    }

    public function test_from_withInvalidValue_throwsValueError(): void
    {
        // Arrange
        $value = 'nonexistent_directive';

        // Assert
        $this->expectException(\ValueError::class);

        // Act
        ProcessorDirective::from($value);
    }

    public function test_enumCases_areBackedByStrings(): void
    {
        // Arrange & Act
        $cases = ProcessorDirective::cases();

        // Assert
        foreach ($cases as $case) {
            $this->assertIsString($case->value);
        }
    }
}
