<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Enums;

use Academe\Elavon\Epg\Psr7\Enums\BatchState;
use PHPUnit\Framework\TestCase;

/**
 * Tests for BatchState enum.
 */
class BatchStateTest extends TestCase
{
    public function test_enumCases_containsAllExpectedStates(): void
    {
        // Arrange & Act
        $cases = BatchState::cases();

        // Assert
        $this->assertCount(5, $cases);
        $this->assertContains(BatchState::SUBMITTED, $cases);
        $this->assertContains(BatchState::SETTLED, $cases);
        $this->assertContains(BatchState::REJECTED, $cases);
        $this->assertContains(BatchState::FAILED, $cases);
        $this->assertContains(BatchState::UNKNOWN, $cases);
    }

    public function test_enumValues_matchApiSpecification(): void
    {
        // Arrange & Act & Assert
        $this->assertSame('submitted', BatchState::SUBMITTED->value);
        $this->assertSame('settled', BatchState::SETTLED->value);
        $this->assertSame('rejected', BatchState::REJECTED->value);
        $this->assertSame('failed', BatchState::FAILED->value);
        $this->assertSame('unknown', BatchState::UNKNOWN->value);
    }

    public function test_tryFrom_withValidValue_returnsCorrectCase(): void
    {
        // Arrange
        $value = 'settled';

        // Act
        $state = BatchState::tryFrom($value);

        // Assert
        $this->assertSame(BatchState::SETTLED, $state);
    }

    public function test_tryFrom_withInvalidValue_returnsNull(): void
    {
        // Arrange
        $value = 'invalid_state';

        // Act
        $state = BatchState::tryFrom($value);

        // Assert
        $this->assertNull($state);
    }

    public function test_from_withValidValue_returnsCorrectCase(): void
    {
        // Arrange
        $value = 'rejected';

        // Act
        $state = BatchState::from($value);

        // Assert
        $this->assertSame(BatchState::REJECTED, $state);
    }

    public function test_from_withInvalidValue_throwsValueError(): void
    {
        // Arrange
        $value = 'nonexistent_state';

        // Assert
        $this->expectException(\ValueError::class);

        // Act
        BatchState::from($value);
    }

    public function test_enumCases_areBackedByStrings(): void
    {
        // Arrange & Act
        $cases = BatchState::cases();

        // Assert
        foreach ($cases as $case) {
            $this->assertIsString($case->value);
        }
    }
}
