<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\Shopper;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\Shopper\DeleteShopperRequest;
use PHPUnit\Framework\TestCase;

class DeleteShopperRequestTest extends TestCase
{
    public function test_construct_withValidId_createsInstance(): void
    {
        $request = new DeleteShopperRequest('shopper_123');

        $this->assertSame('shopper_123', $request->getShopperId());
    }

    public function test_construct_withEmptyId_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DeleteShopperRequest('');
    }

    public function test_build_createsValidPsr7Request(): void
    {
        $request = new DeleteShopperRequest('shopper_456');
        $psr7Request = $request->build();

        $this->assertSame('DELETE', $psr7Request->getMethod());
        $this->assertStringContainsString('/shoppers/shopper_456', (string) $psr7Request->getUri());
    }
}
