<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\Order;

use Academe\Elavon\Epg\Psr7\Dtos\Order;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\Order\UpdateOrderRequest;
use PHPUnit\Framework\TestCase;

class UpdateOrderRequestTest extends TestCase
{
    public function test_construct_withValidIdAndOrder_createsInstance(): void
    {
        $order = new Order(
            total: ['amount' => '100.00', 'currencyCode' => 'USD'],
        );

        $request = new UpdateOrderRequest('ord123', $order);

        $this->assertSame('ord123', $request->getOrderId());
        $this->assertSame($order, $request->getOrder());
    }

    public function test_construct_withArray_normalizesToOrder(): void
    {
        $data = [
            'total' => ['amount' => '200.00', 'currencyCode' => 'EUR'],
            'description' => 'Updated order',
        ];

        $request = new UpdateOrderRequest('ord456', $data);

        $this->assertInstanceOf(Order::class, $request->getOrder());
        $this->assertSame('200.00', $request->getOrder()->total->amount);
    }

    public function test_construct_withEmptyId_throwsException(): void
    {
        $order = new Order(total: ['amount' => '50.00', 'currencyCode' => 'USD']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Order ID cannot be empty');

        new UpdateOrderRequest('', $order);
    }

    public function test_build_createsValidPsr7Request(): void
    {
        $order = new Order(
            total: ['amount' => '150.00', 'currencyCode' => 'GBP'],
            description: 'Updated description',
        );
        $request = new UpdateOrderRequest('ord789', $order);

        $psr7Request = $request->build();

        $this->assertSame('POST', $psr7Request->getMethod());
        $this->assertStringContainsString('/orders/ord789', (string) $psr7Request->getUri());
        $this->assertSame('application/json', $psr7Request->getHeaderLine('Content-Type'));
        $this->assertSame('application/json', $psr7Request->getHeaderLine('Accept'));
    }

    public function test_build_includesOrderDataInBody(): void
    {
        $order = new Order(
            total: ['amount' => '99.99', 'currencyCode' => 'USD'],
            description: 'Modified order',
            customReference: 'REF-999',
        );

        $request = new UpdateOrderRequest('ord111', $order);
        $psr7Request = $request->build();

        $body = (string) $psr7Request->getBody();
        $data = json_decode($body, true);

        $this->assertSame('99.99', $data['total']['amount']);
        $this->assertSame('Modified order', $data['description']);
        $this->assertSame('REF-999', $data['customReference']);
    }

    public function test_build_withCustomBaseUri_usesCustomUri(): void
    {
        $order = new Order(total: ['amount' => '25.00', 'currencyCode' => 'USD']);
        $customUri = 'https://custom.api.example.com';

        $request = new UpdateOrderRequest('ord222', $order, baseUri: $customUri);
        $psr7Request = $request->build();

        $this->assertStringStartsWith($customUri, (string) $psr7Request->getUri());
    }
}
