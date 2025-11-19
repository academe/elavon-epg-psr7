<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Response;

use Academe\Elavon\Epg\Psr7\Dtos\ErrorResponse;
use Academe\Elavon\Epg\Psr7\Dtos\Shopper;
use Academe\Elavon\Epg\Psr7\Messages\Response\ShopperListResponse;
use Academe\Elavon\Epg\Psr7\Support\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ShopperListResponseTest extends TestCase
{
    private function createMockResponse(string $body, int $statusCode): ResponseInterface
    {
        $stream = new Stream($body);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn($statusCode);

        return $response;
    }

    public function test_construct_withSuccessResponse_parsesShopperList(): void
    {
        $responseData = [
            'items' => [
                ['id' => 'shopper_001', 'fullName' => 'Alice Smith'],
                ['id' => 'shopper_002', 'fullName' => 'Bob Jones'],
            ],
            'next' => 'next_page_token',
            'first' => 'first_page_token',
        ];

        $psr7Response = $this->createMockResponse(json_encode($responseData), 200);
        $response = new ShopperListResponse($psr7Response);

        $this->assertTrue($response->isSuccessful());
        $this->assertCount(2, $response->getShoppers());
        $this->assertInstanceOf(Shopper::class, $response->getShoppers()[0]);
        $this->assertSame('shopper_001', $response->getShoppers()[0]->id);
        $this->assertNull($response->getError());
    }

    public function test_parsesPaginationLinks(): void
    {
        $responseData = [
            'items' => [['id' => 'shopper_001']],
            'next' => 'next_token',
            'first' => 'first_token',
        ];

        $psr7Response = $this->createMockResponse(json_encode($responseData), 200);
        $response = new ShopperListResponse($psr7Response);

        $this->assertSame('next_token', $response->getNext());
        $this->assertSame('first_token', $response->getFirst());
        $this->assertTrue($response->hasMorePages());
    }

    public function test_handlesEmptyList(): void
    {
        $responseData = [
            'items' => [],
            'next' => null,
            'first' => null,
        ];

        $psr7Response = $this->createMockResponse(json_encode($responseData), 200);
        $response = new ShopperListResponse($psr7Response);

        $this->assertTrue($response->isSuccessful());
        $this->assertCount(0, $response->getShoppers());
        $this->assertNull($response->getNext());
        $this->assertFalse($response->hasMorePages());
    }

    public function test_construct_withErrorResponse_parsesError(): void
    {
        $errorData = [
            'status' => 401,
            'failures' => [
                ['code' => 'unauthorized', 'description' => 'Unauthorized'],
            ],
        ];

        $psr7Response = $this->createMockResponse(json_encode($errorData), 401);
        $response = new ShopperListResponse($psr7Response);

        $this->assertFalse($response->isSuccessful());
        $this->assertNull($response->getShoppers());
        $this->assertInstanceOf(ErrorResponse::class, $response->getError());
        $this->assertSame(401, $response->getError()->status);
    }
}
