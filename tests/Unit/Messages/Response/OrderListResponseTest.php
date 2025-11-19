<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Response;

use Academe\Elavon\Epg\Psr7\Dtos\Order;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\OrderListResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class OrderListResponseTest extends TestCase
{
    public function test_construct_withSuccessResponse_parsesOrders(): void
    {
        $responseData = [
            'items' => [
                [
                    'id' => 'ord123',
                    'total' => ['amount' => '100.00', 'currencyCode' => 'USD'],
                    'description' => 'Order 1',
                ],
                [
                    'id' => 'ord456',
                    'total' => ['amount' => '200.00', 'currencyCode' => 'EUR'],
                    'description' => 'Order 2',
                ],
            ],
            'next' => 'https://api.example.com/orders?offset=50',
            'first' => 'https://api.example.com/orders',
        ];

        $response = $this->createMockResponse(200, $responseData);
        $listResponse = new OrderListResponse($response);

        $this->assertTrue($listResponse->isSuccessful());
        $this->assertNull($listResponse->getError());
        $this->assertIsArray($listResponse->getOrders());
        $this->assertCount(2, $listResponse->getOrders());
        $this->assertInstanceOf(Order::class, $listResponse->getOrders()[0]);
        $this->assertSame('ord123', $listResponse->getOrders()[0]->id);
        $this->assertSame('ord456', $listResponse->getOrders()[1]->id);
    }

    public function test_construct_withEmptyItems_parsesSuccessfully(): void
    {
        $responseData = [
            'items' => [],
        ];

        $response = $this->createMockResponse(200, $responseData);
        $listResponse = new OrderListResponse($response);

        $this->assertTrue($listResponse->isSuccessful());
        $this->assertIsArray($listResponse->getOrders());
        $this->assertCount(0, $listResponse->getOrders());
    }

    public function test_construct_withPaginationLinks_storesLinks(): void
    {
        $responseData = [
            'items' => [
                ['id' => 'ord1', 'total' => ['amount' => '10.00', 'currencyCode' => 'USD']],
            ],
            'next' => 'https://api.example.com/orders?page=2',
            'first' => 'https://api.example.com/orders?page=1',
        ];

        $response = $this->createMockResponse(200, $responseData);
        $listResponse = new OrderListResponse($response);

        $this->assertSame('https://api.example.com/orders?page=2', $listResponse->getNext());
        $this->assertSame('https://api.example.com/orders?page=1', $listResponse->getFirst());
        $this->assertTrue($listResponse->hasMorePages());
    }

    public function test_construct_withoutNextLink_hasNoMorePages(): void
    {
        $responseData = [
            'items' => [
                ['id' => 'ord1', 'total' => ['amount' => '10.00', 'currencyCode' => 'USD']],
            ],
        ];

        $response = $this->createMockResponse(200, $responseData);
        $listResponse = new OrderListResponse($response);

        $this->assertNull($listResponse->getNext());
        $this->assertFalse($listResponse->hasMorePages());
    }

    public function test_construct_withErrorResponse_parsesError(): void
    {
        $errorData = [
            'status' => 401,
            'failures' => [
                [
                    'code' => 'UNAUTHORIZED',
                    'description' => 'Unauthorized access',
                    'field' => null,
                ],
            ],
        ];

        $response = $this->createMockResponse(401, $errorData);
        $listResponse = new OrderListResponse($response);

        $this->assertFalse($listResponse->isSuccessful());
        $this->assertNull($listResponse->getOrders());
        $this->assertNotNull($listResponse->getError());
        $this->assertSame('Unauthorized access', $listResponse->getError()->getMessage());
    }

    public function test_construct_withMissingItemsKey_throwsException(): void
    {
        $responseData = [
            'data' => [],  // Wrong key
        ];

        $response = $this->createMockResponse(200, $responseData);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response must contain an "items" array');

        new OrderListResponse($response);
    }

    public function test_construct_withNonArrayItems_throwsException(): void
    {
        $responseData = [
            'items' => 'not an array',
        ];

        $response = $this->createMockResponse(200, $responseData);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response must contain an "items" array');

        new OrderListResponse($response);
    }

    public function test_construct_withInvalidItemData_throwsException(): void
    {
        $responseData = [
            'items' => [
                ['id' => 'ord1', 'total' => ['amount' => '10.00', 'currencyCode' => 'USD']],
                'invalid item',  // String instead of array
            ],
        ];

        $response = $this->createMockResponse(200, $responseData);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Item at index 1 is not an array');

        new OrderListResponse($response);
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

        new OrderListResponse($response);
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

        new OrderListResponse($response);
    }

    public function test_fromPsr7Response_createsInstance(): void
    {
        $responseData = [
            'items' => [
                ['id' => 'ord789', 'total' => ['amount' => '50.00', 'currencyCode' => 'GBP']],
            ],
        ];

        $response = $this->createMockResponse(200, $responseData);
        $listResponse = OrderListResponse::fromPsr7Response($response);

        $this->assertInstanceOf(OrderListResponse::class, $listResponse);
        $this->assertCount(1, $listResponse->getOrders());
    }

    public function test_getStatusCode_returnsCorrectCode(): void
    {
        $responseData = [
            'items' => [
                ['id' => 'ord999', 'total' => ['amount' => '25.00', 'currencyCode' => 'USD']],
            ],
        ];

        $response = $this->createMockResponse(200, $responseData);
        $listResponse = new OrderListResponse($response);

        $this->assertSame(200, $listResponse->getStatusCode());
    }

    public function test_getPsr7Response_returnsOriginalResponse(): void
    {
        $responseData = [
            'items' => [],
        ];

        $response = $this->createMockResponse(200, $responseData);
        $listResponse = new OrderListResponse($response);

        $this->assertSame($response, $listResponse->getPsr7Response());
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
