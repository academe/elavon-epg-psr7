<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Enums;

use Academe\Elavon\Epg\Psr7\Enums\Source;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Source enum.
 */
class SourceTest extends TestCase
{
    public function test_enumCases_containsAllExpectedSources(): void
    {
        // Arrange & Act
        $cases = Source::cases();

        // Assert
        $this->assertCount(12, $cases);
        $this->assertContains(Source::DIRECT_API_CALL, $cases);
        $this->assertContains(Source::HPP_SUBMIT_REDIRECT, $cases);
        $this->assertContains(Source::HPP_IFRAME_LIGHTBOX, $cases);
        $this->assertContains(Source::HPP_IFRAME_EMBEDDED, $cases);
        $this->assertContains(Source::HPP_SDK, $cases);
        $this->assertContains(Source::VIRTUAL_TERMINAL, $cases);
        $this->assertContains(Source::GATEWAY_RECURRING, $cases);
        $this->assertContains(Source::PAY_BY_LINK, $cases);
        $this->assertContains(Source::MONITORING, $cases);
        $this->assertContains(Source::HPP_FIELDS, $cases);
        $this->assertContains(Source::PHYSICAL_TERMINAL, $cases);
        $this->assertContains(Source::UNKNOWN, $cases);
    }

    public function test_enumValues_matchApiSpecification(): void
    {
        // Arrange & Act & Assert
        $this->assertSame('directApiCall', Source::DIRECT_API_CALL->value);
        $this->assertSame('hppSubmitRedirect', Source::HPP_SUBMIT_REDIRECT->value);
        $this->assertSame('hppIframeLightbox', Source::HPP_IFRAME_LIGHTBOX->value);
        $this->assertSame('hppIframeEmbedded', Source::HPP_IFRAME_EMBEDDED->value);
        $this->assertSame('hppSdk', Source::HPP_SDK->value);
        $this->assertSame('virtualTerminal', Source::VIRTUAL_TERMINAL->value);
        $this->assertSame('gatewayRecurring', Source::GATEWAY_RECURRING->value);
        $this->assertSame('payByLink', Source::PAY_BY_LINK->value);
        $this->assertSame('monitoring', Source::MONITORING->value);
        $this->assertSame('hppFields', Source::HPP_FIELDS->value);
        $this->assertSame('physicalTerminal', Source::PHYSICAL_TERMINAL->value);
        $this->assertSame('unknown', Source::UNKNOWN->value);
    }

    public function test_tryFrom_withValidValue_returnsCorrectCase(): void
    {
        // Arrange
        $value = 'directApiCall';

        // Act
        $source = Source::tryFrom($value);

        // Assert
        $this->assertSame(Source::DIRECT_API_CALL, $source);
    }

    public function test_tryFrom_withInvalidValue_returnsNull(): void
    {
        // Arrange
        $value = 'invalid_source';

        // Act
        $source = Source::tryFrom($value);

        // Assert
        $this->assertNull($source);
    }

    public function test_from_withValidValue_returnsCorrectCase(): void
    {
        // Arrange
        $value = 'virtualTerminal';

        // Act
        $source = Source::from($value);

        // Assert
        $this->assertSame(Source::VIRTUAL_TERMINAL, $source);
    }

    public function test_from_withInvalidValue_throwsValueError(): void
    {
        // Arrange
        $value = 'nonexistent_source';

        // Assert
        $this->expectException(\ValueError::class);

        // Act
        Source::from($value);
    }

    public function test_enumCases_areBackedByStrings(): void
    {
        // Arrange & Act
        $cases = Source::cases();

        // Assert
        foreach ($cases as $case) {
            $this->assertIsString($case->value);
        }
    }
}
