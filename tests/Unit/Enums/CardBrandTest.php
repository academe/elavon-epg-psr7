<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Enums;

use Academe\Elavon\Epg\Psr7\Enums\CardBrand;
use PHPUnit\Framework\TestCase;

/**
 * Tests for CardBrand enum.
 */
class CardBrandTest extends TestCase
{
    public function test_hasCorrectValues(): void
    {
        $this->assertSame('American Express', CardBrand::AMERICAN_EXPRESS->value);
        $this->assertSame('UnionPay Credit', CardBrand::UNION_PAY_CREDIT->value);
        $this->assertSame('UnionPay Debit', CardBrand::UNION_PAY_DEBIT->value);
        $this->assertSame('Diners Club', CardBrand::DINERS_CLUB->value);
        $this->assertSame('Discover', CardBrand::DISCOVER->value);
        $this->assertSame('JCB', CardBrand::JCB->value);
        $this->assertSame('Maestro', CardBrand::MAESTRO->value);
        $this->assertSame('MasterCard', CardBrand::MASTERCARD->value);
        $this->assertSame('MasterCard Credit', CardBrand::MASTERCARD_CREDIT->value);
        $this->assertSame('MasterCard Debit', CardBrand::MASTERCARD_DEBIT->value);
        $this->assertSame('Visa', CardBrand::VISA->value);
        $this->assertSame('Visa Debit', CardBrand::VISA_DEBIT->value);
        $this->assertSame('Visa Credit', CardBrand::VISA_CREDIT->value);
        $this->assertSame('Visa Electron', CardBrand::VISA_ELECTRON->value);
    }

    public function test_from_withValidValue_returnsEnum(): void
    {
        $this->assertSame(CardBrand::VISA, CardBrand::from('Visa'));
        $this->assertSame(CardBrand::MASTERCARD, CardBrand::from('MasterCard'));
        $this->assertSame(CardBrand::AMERICAN_EXPRESS, CardBrand::from('American Express'));
    }

    public function test_from_withInvalidValue_throwsException(): void
    {
        $this->expectException(\ValueError::class);
        CardBrand::from('Invalid');
    }

    public function test_tryFrom_withValidValue_returnsEnum(): void
    {
        $this->assertSame(CardBrand::VISA, CardBrand::tryFrom('Visa'));
        $this->assertSame(CardBrand::MAESTRO, CardBrand::tryFrom('Maestro'));
    }

    public function test_tryFrom_withInvalidValue_returnsNull(): void
    {
        $this->assertNull(CardBrand::tryFrom('Invalid'));
    }
}
