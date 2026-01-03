<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\Order;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\Order\RetrieveOrderRequest;
use PHPUnit\Framework\TestCase;

class RetrieveOrderRequestTest extends TestCase
{
    // public function test_fromData_withMissingOrderIdKey_throwsException(): void
    // {
    //     $this->expectException(InvalidArgumentException::class);
    //     $this->expectExceptionMessage("Missing required key 'orderId' in data");

    //     RetrieveOrderRequest::fromData([]);
    // }

    public function test_construct_withValidId_createsInstance(): void
    {
        $request = new RetrieveOrderRequest('order123');

        $this->assertSame('order123', $request->orderId);
    }

    // public function test_construct_withEmptyId_throwsException(): void
    // {
    //     $this->expectException(InvalidArgumentException::class);
    //     $this->expectExceptionMessage('Order ID cannot be empty');

    //     new RetrieveOrderRequest('');
    // }

    public function test_fromData_createsInstance(): void
    {
        $request = RetrieveOrderRequest::fromData(['orderId' => 'order123']);

        $this->assertSame('order123', $request->orderId);
    }

    public function test_build_createsValidPsr7Request(): void
    {
        $request = new RetrieveOrderRequest('ord456');

        $psr7Request = $request->build();

        $this->assertSame('GET', $psr7Request->getMethod());
        $this->assertStringContainsString('/orders/ord456', (string) $psr7Request->getUri());
    }
}