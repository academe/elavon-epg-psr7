<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Enums;

use Academe\Elavon\Epg\Psr7\Enums\TerminalType;
use PHPUnit\Framework\TestCase;

/**
 * Tests for TerminalType enum.
 */
class TerminalTypeTest extends TestCase
{
    public function test_enum_hasCorrectValues(): void
    {
        $this->assertSame('hardware', TerminalType::HARDWARE->value);
        $this->assertSame('software', TerminalType::SOFTWARE->value);
    }

    public function test_from_withValidValue_returnsEnum(): void
    {
        $this->assertSame(TerminalType::HARDWARE, TerminalType::from('hardware'));
        $this->assertSame(TerminalType::SOFTWARE, TerminalType::from('software'));
    }

    public function test_tryFrom_withInvalidValue_returnsNull(): void
    {
        $this->assertNull(TerminalType::tryFrom('invalid'));
    }

    public function test_cases_returnsAllCases(): void
    {
        $cases = TerminalType::cases();

        $this->assertCount(2, $cases);
        $this->assertContains(TerminalType::HARDWARE, $cases);
        $this->assertContains(TerminalType::SOFTWARE, $cases);
    }
}
