<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Enums;

use Academe\Elavon\Epg\Psr7\Enums\ShopperInteraction;
use PHPUnit\Framework\TestCase;

/**
 * Tests for ShopperInteraction enum.
 */
class ShopperInteractionTest extends TestCase
{
    public function test_enumCases_containsAllExpectedInteractions(): void
    {
        // Arrange & Act
        $cases = ShopperInteraction::cases();

        // Assert
        $this->assertCount(5, $cases);
        $this->assertContains(ShopperInteraction::ECOMMERCE, $cases);
        $this->assertContains(ShopperInteraction::MAIL_ORDER, $cases);
        $this->assertContains(ShopperInteraction::TELEPHONE_ORDER, $cases);
        $this->assertContains(ShopperInteraction::MERCHANT_INITIATED, $cases);
        $this->assertContains(ShopperInteraction::IN_PERSON, $cases);
    }

    public function test_enumValues_matchApiSpecification(): void
    {
        // Arrange & Act & Assert
        $this->assertSame('ecommerce', ShopperInteraction::ECOMMERCE->value);
        $this->assertSame('mailOrder', ShopperInteraction::MAIL_ORDER->value);
        $this->assertSame('telephoneOrder', ShopperInteraction::TELEPHONE_ORDER->value);
        $this->assertSame('merchantInitiated', ShopperInteraction::MERCHANT_INITIATED->value);
        $this->assertSame('inPerson', ShopperInteraction::IN_PERSON->value);
    }

    public function test_tryFrom_withValidValue_returnsCorrectCase(): void
    {
        // Arrange
        $value = 'ecommerce';

        // Act
        $interaction = ShopperInteraction::tryFrom($value);

        // Assert
        $this->assertSame(ShopperInteraction::ECOMMERCE, $interaction);
    }

    public function test_tryFrom_withInvalidValue_returnsNull(): void
    {
        // Arrange
        $value = 'invalid_interaction';

        // Act
        $interaction = ShopperInteraction::tryFrom($value);

        // Assert
        $this->assertNull($interaction);
    }

    public function test_from_withValidValue_returnsCorrectCase(): void
    {
        // Arrange
        $value = 'merchantInitiated';

        // Act
        $interaction = ShopperInteraction::from($value);

        // Assert
        $this->assertSame(ShopperInteraction::MERCHANT_INITIATED, $interaction);
    }

    public function test_from_withInvalidValue_throwsValueError(): void
    {
        // Arrange
        $value = 'nonexistent_interaction';

        // Assert
        $this->expectException(\ValueError::class);

        // Act
        ShopperInteraction::from($value);
    }

    public function test_enumCases_areBackedByStrings(): void
    {
        // Arrange & Act
        $cases = ShopperInteraction::cases();

        // Assert
        foreach ($cases as $case) {
            $this->assertIsString($case->value);
        }
    }
}
