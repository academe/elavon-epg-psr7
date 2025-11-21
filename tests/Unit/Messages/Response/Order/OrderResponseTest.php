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
    public function test_construct_withSuccessResponse_parsesOrder(): void
    {
        $responseData = [
            'id' => 'ord123',
            'href' => 'https://api.example.com/orders/ord123',
            'total' => ['amount' => '100.00', 'currencyCode' => 'USD'],
            'description' => 'Test order',
            'createdAt' => '2025-11-19T10:00:00Z',
        ];

        $response = $this->createMockResponse(200, $responseData);
        $orderResponse = new OrderResponse($response);

        $this->assertTrue($orderResponse->isSuccessful());
        $this->assertNull($orderResponse->getError());
        $this->assertInstanceOf(Order::class, $orderResponse->getOrder());
        $this->assertSame('ord123', $orderResponse->getOrder()->id);
        $this->assertSame('100.00', $orderResponse->getOrder()->total->amount);
        $this->assertSame('Test order', $orderResponse->getOrder()->description);
    }

    public function test_construct_withSuccessResponseAndItems_parsesOrder(): void
    {
        $responseData = [
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

        $response = $this->createMockResponse(200, $responseData);
        $orderResponse = new OrderResponse($response);

        $this->assertTrue($orderResponse->isSuccessful());
        $order = $orderResponse->getOrder();
        $this->assertNotNull($order->items);
        $this->assertCount(2, $order->items);
        $this->assertSame('Item 1', $order->items[0]->description);
        $this->assertSame('Item 2', $order->items[1]->description);
    }

    public function test_construct_withErrorResponse_parsesError(): void
    {
        $errorData = [
            'status' => 404,
            'failures' => [
                [
                    'code' => 'ORDER_NOT_FOUND',
                    'description' => 'Order not found',
                    'field' => null,
                ],
            ],
        ];

        $response = $this->createMockResponse(404, $errorData);
        $orderResponse = new OrderResponse($response);

        $this->assertFalse($orderResponse->isSuccessful());
        $this->assertNull($orderResponse->getOrder());
        $this->assertNotNull($orderResponse->getError());
        $this->assertSame('Order not found', $orderResponse->getError()->getMessage());
    }

    public function test_construct_withEmptyBody_throwsException(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $stream->method('__toString')->willReturn('');
        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn(200);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response body is empty');

        new OrderResponse($response);
    }

    public function test_construct_withInvalidJson_throwsException(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $stream->method('__toString')->willReturn('invalid json{');
        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn(200);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed to decode JSON response');

        new OrderResponse($response);
    }

    public function test_construct_withJsonArray_throwsException(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $stream->method('__toString')->willReturn('[]');
        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn(200);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response body is not a JSON object');

        new OrderResponse($response);
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
        $this->assertSame('ord789', $orderResponse->getOrder()->id);
    }

    public function test_getStatusCode_returnsCorrectCode(): void
    {
        $responseData = [
            'id' => 'ord999',
            'total' => ['amount' => '25.00', 'currencyCode' => 'USD'],
        ];

        $response = $this->createMockResponse(201, $responseData);
        $orderResponse = new OrderResponse($response);

        $this->assertSame(201, $orderResponse->getStatusCode());
    }

    public function test_getPsr7Response_returnsOriginalResponse(): void
    {
        $responseData = [
            'id' => 'ord111',
            'total' => ['amount' => '75.00', 'currencyCode' => 'USD'],
        ];

        $response = $this->createMockResponse(200, $responseData);
        $orderResponse = new OrderResponse($response);

        $this->assertSame($response, $orderResponse->getPsr7Response());
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
