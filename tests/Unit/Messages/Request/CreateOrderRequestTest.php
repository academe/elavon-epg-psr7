<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request;

use Academe\Elavon\Epg\Psr7\Dtos\Order;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\CreateOrderRequest;
use PHPUnit\Framework\TestCase;

class CreateOrderRequestTest extends TestCase
{
    public function test_construct_withOrderObject_createsInstance(): void
    {
        $order = new Order(
            total: ['amount' => '100.00', 'currencyCode' => 'USD'],
            description: 'Test order',
        );

        $request = new CreateOrderRequest($order);

        $this->assertSame($order, $request->getOrder());
    }

    public function test_construct_withArray_normalizesToOrder(): void
    {
        $data = [
            'total' => ['amount' => '150.00', 'currencyCode' => 'EUR'],
            'description' => 'Array order',
        ];

        $request = new CreateOrderRequest($data);

        $this->assertInstanceOf(Order::class, $request->getOrder());
        $this->assertSame('150.00', $request->getOrder()->total->amount);
    }

    public function test_construct_withoutTotal_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Order total is required for creating an order');

        new CreateOrderRequest(['description' => 'No total']);
    }

    public function test_build_createsValidPsr7Request(): void
    {
        $order = new Order(
            total: ['amount' => '200.00', 'currencyCode' => 'GBP'],
        );
        $request = new CreateOrderRequest($order);

        $psr7Request = $request->build();

        $this->assertSame('POST', $psr7Request->getMethod());
        $this->assertStringContainsString('/orders', (string) $psr7Request->getUri());
        $this->assertSame('application/json', $psr7Request->getHeaderLine('Content-Type'));
        $this->assertSame('application/json', $psr7Request->getHeaderLine('Accept'));
    }

    public function test_build_includesOrderDataInBody(): void
    {
        $order = new Order(
            total: ['amount' => '75.50', 'currencyCode' => 'USD'],
            description: 'Premium service',
            shopperEmailAddress: 'customer@example.com',
        );

        $request = new CreateOrderRequest($order);
        $psr7Request = $request->build();

        $body = (string) $psr7Request->getBody();
        $data = json_decode($body, true);

        $this->assertSame('75.50', $data['total']['amount']);
        $this->assertSame('USD', $data['total']['currencyCode']);
        $this->assertSame('Premium service', $data['description']);
        $this->assertSame('customer@example.com', $data['shopperEmailAddress']);
    }

    public function test_build_withItems_includesItemsInBody(): void
    {
        $order = new Order(
            total: ['amount' => '300.00', 'currencyCode' => 'USD'],
            items: [
                [
                    'total' => ['amount' => '150.00', 'currencyCode' => 'USD'],
                    'description' => 'Item 1',
                    'type' => 'goods',
                ],
                [
                    'total' => ['amount' => '150.00', 'currencyCode' => 'USD'],
                    'description' => 'Item 2',
                    'type' => 'service',
                ],
            ],
        );

        $request = new CreateOrderRequest($order);
        $psr7Request = $request->build();

        $body = (string) $psr7Request->getBody();
        $data = json_decode($body, true);

        $this->assertIsArray($data['items']);
        $this->assertCount(2, $data['items']);
        $this->assertSame('Item 1', $data['items'][0]['description']);
        $this->assertSame('goods', $data['items'][0]['type']);
        $this->assertSame('Item 2', $data['items'][1]['description']);
        $this->assertSame('service', $data['items'][1]['type']);
    }

    public function test_build_withCustomBaseUri_usesCustomUri(): void
    {
        $order = new Order(total: ['amount' => '50.00', 'currencyCode' => 'USD']);
        $customUri = 'https://custom.api.example.com';

        $request = new CreateOrderRequest($order, baseUri: $customUri);
        $psr7Request = $request->build();

        $this->assertStringStartsWith($customUri, (string) $psr7Request->getUri());
    }
}
