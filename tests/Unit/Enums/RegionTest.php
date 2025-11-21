<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Enums;

use Academe\Elavon\Epg\Psr7\Enums\Region;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Region enum.
 */
class RegionTest extends TestCase
{
    public function test_hasCorrectValues(): void
    {
        $this->assertSame('eu', Region::EU->value);
        $this->assertSame('na', Region::NA->value);
        $this->assertSame('unknown', Region::UNKNOWN->value);
    }

    public function test_from_withValidValue_returnsEnum(): void
    {
        $this->assertSame(Region::EU, Region::from('eu'));
        $this->assertSame(Region::NA, Region::from('na'));
        $this->assertSame(Region::UNKNOWN, Region::from('unknown'));
    }

    public function test_from_withInvalidValue_throwsException(): void
    {
        $this->expectException(\ValueError::class);
        Region::from('invalid');
    }

    public function test_tryFrom_withValidValue_returnsEnum(): void
    {
        $this->assertSame(Region::EU, Region::tryFrom('eu'));
        $this->assertSame(Region::NA, Region::tryFrom('na'));
    }

    public function test_tryFrom_withInvalidValue_returnsNull(): void
    {
        $this->assertNull(Region::tryFrom('invalid'));
    }
}
