<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\Shopper;

use Academe\Elavon\Epg\Psr7\Dtos\Shopper;
use Academe\Elavon\Epg\Psr7\Messages\Request\Shopper\CreateShopperRequest;
use PHPUnit\Framework\TestCase;

class CreateShopperRequestTest extends TestCase
{
    public function test_construct_withShopperObject_createsInstance(): void
    {
        $shopper = new Shopper(fullName: 'John Doe', email: 'john@example.com');

        $request = new CreateShopperRequest($shopper);

        $this->assertSame($shopper, $request->getShopper());
    }

    public function test_construct_withArray_normalizes(): void
    {
        $data = ['fullName' => 'Jane Doe', 'email' => 'jane@example.com'];

        $request = new CreateShopperRequest($data);

        $this->assertInstanceOf(Shopper::class, $request->getShopper());
    }

    public function test_build_createsValidPsr7Request(): void
    {
        $shopper = new Shopper(fullName: 'Test User');
        $request = new CreateShopperRequest($shopper);

        $psr7Request = $request->build();

        $this->assertSame('POST', $psr7Request->getMethod());
        $this->assertStringContainsString('/shoppers', (string) $psr7Request->getUri());
    }

    public function test_build_includesShopperDataInBody(): void
    {
        $shopper = new Shopper(fullName: 'Alice Smith', email: 'alice@test.com');

        $request = new CreateShopperRequest($shopper);
        $psr7Request = $request->build();

        $body = (string) $psr7Request->getBody();
        $data = json_decode($body, true);

        $this->assertSame('Alice Smith', $data['fullName']);
        $this->assertSame('alice@test.com', $data['email']);
    }
}
