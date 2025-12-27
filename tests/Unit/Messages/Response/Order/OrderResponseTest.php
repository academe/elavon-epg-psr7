<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Response\Order;

use Academe\Elavon\Epg\Psr7\Dtos\Order;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Order\OrderResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class OrderResponseTest extends TestCase
{
    public function test_construct_withSuccessData_parsesOrder(): void
    {
        $data = [
            'id' => 'ord123',
            'href' => 'https://api.example.com/orders/ord123',
            'total' => ['amount' => '100.00', 'currencyCode' => 'USD'],
            'description' => 'Test order',
            'createdAt' => '2025-11-19T10:00:00Z',
        ];

        $orderResponse = new OrderResponse($data, 200);

        $this->assertTrue($orderResponse->isSuccessful());
        $this->assertNull($orderResponse->error);
        $this->assertInstanceOf(Order::class, $orderResponse->order);
        $this->assertSame('ord123', $orderResponse->order->id);
        $this->assertSame('10000', $orderResponse->order->total->getAmount());
        $this->assertSame('Test order', $orderResponse->order->description);
    }

    public function test_construct_withSuccessDataAndItems_parsesOrder(): void
    {
        $data = [
            'id' => 'ord456',
            'total' => ['amount' => '300.00', 'currencyCode' => 'EUR'],
            'items' => [
                [
                    'total' => ['amount' => '150.00', 'currencyCode' => 'EUR'],
                    'description' => 'Item 1',
                    'type' => 'goods',
                ],
                [
                    'total' => ['amount' => '150.00', 'currencyCode' => 'EUR'],
                    'description' => 'Item 2',
                    'type' => 'service',
                ],
            ],
        ];

        $orderResponse = new OrderResponse($data, 200);

        $this->assertTrue($orderResponse->isSuccessful());
        $order = $orderResponse->order;
        $this->assertNotNull($order->items);
        $this->assertCount(2, $order->items);
        $this->assertSame('Item 1', $order->items[0]->description);
        $this->assertSame('Item 2', $order->items[1]->description);
    }

    public function test_construct_withErrorData_parsesError(): void
    {
        $data = [
            'status' => 404,
            'failures' => [
                [
                    'code' => 'ORDER_NOT_FOUND',
                    'description' => 'Order not found',
                    'field' => null,
                ],
            ],
        ];

        $orderResponse = new OrderResponse($data, 404);

        $this->assertFalse($orderResponse->isSuccessful());
        $this->assertNull($orderResponse->order);
        $this->assertNotNull($orderResponse->error);
        $this->assertSame('Order not found', $orderResponse->error->getMessage());
    }

    public function test_fromPsr7Response_withEmptyBody_throwsException(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $stream->method('__toString')->willReturn('');
        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn(200);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response body is empty');

        OrderResponse::fromPsr7Response($response);
    }

    public function test_fromPsr7Response_withInvalidJson_throwsException(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $stream->method('__toString')->willReturn('invalid json{');
        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn(200);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed to decode JSON response');

        OrderResponse::fromPsr7Response($response);
    }

    public function test_fromPsr7Response_withJsonArray_throwsException(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $stream->method('__toString')->willReturn('[]');
        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn(200);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response body is not a JSON object');

        OrderResponse::fromPsr7Response($response);
    }

    public function test_fromPsr7Response_createsInstance(): void
    {
        $responseData = [
            'id' => 'ord789',
            'total' => ['amount' => '50.00', 'currencyCode' => 'GBP'],
        ];

        $response = $this->createMockResponse(200, $responseData);
        $orderResponse = OrderResponse::fromPsr7Response($response);

        $this->assertInstanceOf(OrderResponse::class, $orderResponse);
        $this->assertSame('ord789', $orderResponse->order->id);
    }

    public function test_getStatusCode_returnsCorrectCode(): void
    {
        $data = [
            'id' => 'ord999',
            'total' => ['amount' => '25.00', 'currencyCode' => 'USD'],
        ];

        $orderResponse = new OrderResponse($data, 201);

        $this->assertSame(201, $orderResponse->statusCode);
    }

    /**
     * Creates a mock PSR-7 response.
     */
    private function createMockResponse(int $statusCode, array $data): ResponseInterface
    {
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $json = json_encode($data);
        $stream->method('__toString')->willReturn($json);

        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn($statusCode);

        return $response;
    }
}
