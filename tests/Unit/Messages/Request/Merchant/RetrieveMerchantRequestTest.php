<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\Merchant;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\Merchant\RetrieveMerchantRequest;
use PHPUnit\Framework\TestCase;

class RetrieveMerchantRequestTest extends TestCase
{
    public function test_construct_withValidId_createsInstance(): void
    {
        $request = new RetrieveMerchantRequest('merchant123');

        $this->assertSame('merchant123', $request->merchantId);
    }

    public function test_construct_withEmptyId_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Merchant ID cannot be empty');

        new RetrieveMerchantRequest('');
    }

    public function test_build_createsValidPsr7Request(): void
    {
        $request = new RetrieveMerchantRequest('merchant456');

        $psr7Request = $request->build();

        $this->assertSame('GET', $psr7Request->getMethod());
        $this->assertStringContainsString('/merchants/merchant456', (string) $psr7Request->getUri());
    }
}
