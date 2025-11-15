<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Enums;

use Academe\Elavon\Epg\Psr7\Enums\CardScheme;
use PHPUnit\Framework\TestCase;

/**
 * Tests for CardScheme enum.
 */
class CardSchemeTest extends TestCase
{
    public function test_enumCases_containsAllExpectedSchemes(): void
    {
        // Arrange & Act
        $cases = CardScheme::cases();

        // Assert
        $this->assertCount(9, $cases);
        $this->assertContains(CardScheme::VISA, $cases);
        $this->assertContains(CardScheme::MASTERCARD, $cases);
        $this->assertContains(CardScheme::AMERICAN_EXPRESS, $cases);
        $this->assertContains(CardScheme::DISCOVER, $cases);
        $this->assertContains(CardScheme::DINERS_CLUB, $cases);
        $this->assertContains(CardScheme::JCB, $cases);
        $this->assertContains(CardScheme::MAESTRO, $cases);
        $this->assertContains(CardScheme::UNION_PAY, $cases);
        $this->assertContains(CardScheme::UNKNOWN, $cases);
    }

    public function test_enumValues_matchApiSpecification(): void
    {
        // Arrange & Act & Assert
        $this->assertSame('Visa', CardScheme::VISA->value);
        $this->assertSame('MasterCard', CardScheme::MASTERCARD->value);
        $this->assertSame('American Express', CardScheme::AMERICAN_EXPRESS->value);
        $this->assertSame('Discover', CardScheme::DISCOVER->value);
        $this->assertSame('Diners Club', CardScheme::DINERS_CLUB->value);
        $this->assertSame('JCB', CardScheme::JCB->value);
        $this->assertSame('Maestro', CardScheme::MAESTRO->value);
        $this->assertSame('UnionPay', CardScheme::UNION_PAY->value);
        $this->assertSame('Unknown', CardScheme::UNKNOWN->value);
    }

    public function test_tryFrom_withValidValue_returnsCorrectCase(): void
    {
        // Arrange
        $value = 'Visa';

        // Act
        $scheme = CardScheme::tryFrom($value);

        // Assert
        $this->assertSame(CardScheme::VISA, $scheme);
    }

    public function test_tryFrom_withInvalidValue_returnsNull(): void
    {
        // Arrange
        $value = 'InvalidScheme';

        // Act
        $scheme = CardScheme::tryFrom($value);

        // Assert
        $this->assertNull($scheme);
    }

    public function test_from_withValidValue_returnsCorrectCase(): void
    {
        // Arrange
        $value = 'MasterCard';

        // Act
        $scheme = CardScheme::from($value);

        // Assert
        $this->assertSame(CardScheme::MASTERCARD, $scheme);
    }

    public function test_from_withInvalidValue_throwsValueError(): void
    {
        // Arrange
        $value = 'NonexistentScheme';

        // Assert
        $this->expectException(\ValueError::class);

        // Act
        CardScheme::from($value);
    }

    public function test_enumCases_areBackedByStrings(): void
    {
        // Arrange & Act
        $cases = CardScheme::cases();

        // Assert
        foreach ($cases as $case) {
            $this->assertIsString($case->value);
        }
    }

    public function test_americanExpress_hasCorrectValue(): void
    {
        // Arrange & Act
        $scheme = CardScheme::AMERICAN_EXPRESS;

        // Assert
        $this->assertSame('American Express', $scheme->value);
    }

    public function test_dinersClub_hasCorrectValue(): void
    {
        // Arrange & Act
        $scheme = CardScheme::DINERS_CLUB;

        // Assert
        $this->assertSame('Diners Club', $scheme->value);
    }
}