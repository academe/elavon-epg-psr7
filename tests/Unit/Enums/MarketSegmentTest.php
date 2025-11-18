<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Enums;

use Academe\Elavon\Epg\Psr7\Enums\MarketSegment;
use PHPUnit\Framework\TestCase;

/**
 * Tests for MarketSegment enum.
 */
class MarketSegmentTest extends TestCase
{
    public function test_enumCases_containsAllExpectedSegments(): void
    {
        // Arrange & Act
        $cases = MarketSegment::cases();

        // Assert
        $this->assertCount(2, $cases);
        $this->assertContains(MarketSegment::RETAIL, $cases);
        $this->assertContains(MarketSegment::RESTAURANT, $cases);
    }

    public function test_enumValues_matchApiSpecification(): void
    {
        // Arrange & Act & Assert
        $this->assertSame('retail', MarketSegment::RETAIL->value);
        $this->assertSame('restaurant', MarketSegment::RESTAURANT->value);
    }

    public function test_tryFrom_withValidValue_returnsCorrectCase(): void
    {
        // Arrange
        $value = 'retail';

        // Act
        $segment = MarketSegment::tryFrom($value);

        // Assert
        $this->assertSame(MarketSegment::RETAIL, $segment);
    }

    public function test_tryFrom_withInvalidValue_returnsNull(): void
    {
        // Arrange
        $value = 'invalid_segment';

        // Act
        $segment = MarketSegment::tryFrom($value);

        // Assert
        $this->assertNull($segment);
    }

    public function test_from_withValidValue_returnsCorrectCase(): void
    {
        // Arrange
        $value = 'restaurant';

        // Act
        $segment = MarketSegment::from($value);

        // Assert
        $this->assertSame(MarketSegment::RESTAURANT, $segment);
    }

    public function test_from_withInvalidValue_throwsValueError(): void
    {
        // Arrange
        $value = 'nonexistent_segment';

        // Assert
        $this->expectException(\ValueError::class);

        // Act
        MarketSegment::from($value);
    }

    public function test_enumCases_areBackedByStrings(): void
    {
        // Arrange & Act
        $cases = MarketSegment::cases();

        // Assert
        foreach ($cases as $case) {
            $this->assertIsString($case->value);
        }
    }
}
