<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\Shopper;

use Academe\Elavon\Epg\Psr7\Dtos\Shopper;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\Shopper\UpdateShopperRequest;
use PHPUnit\Framework\TestCase;

class UpdateShopperRequestTest extends TestCase
{
    public function test_construct_withValidData_createsInstance(): void
    {
        $shopper = new Shopper(email: 'updated@example.com');

        $request = new UpdateShopperRequest('shopper_123', $shopper);

        $this->assertSame('shopper_123', $request->getShopperId());
        $this->assertSame($shopper, $request->getUpdates());
    }

    public function test_construct_withEmptyId_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UpdateShopperRequest('', new Shopper());
    }

    public function test_build_createsValidPsr7Request(): void
    {
        $shopper = new Shopper(fullName: 'Updated Name');
        $request = new UpdateShopperRequest('shopper_789', $shopper);

        $psr7Request = $request->build();

        $this->assertSame('PATCH', $psr7Request->getMethod());
        $this->assertStringContainsString('/shoppers/shopper_789', (string) $psr7Request->getUri());
    }
}
