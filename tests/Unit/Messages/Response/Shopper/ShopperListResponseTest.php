<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Response\Shopper;

use Academe\Elavon\Epg\Psr7\Dtos\ErrorResponse;
use Academe\Elavon\Epg\Psr7\Dtos\Shopper;
use Academe\Elavon\Epg\Psr7\Messages\Response\Shopper\ShopperListResponse;
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

        $response = new ShopperListResponse($responseData, 200);

        $this->assertTrue($response->isSuccessful());
        $this->assertCount(2, $response->shoppers);
        $this->assertInstanceOf(Shopper::class, $response->shoppers[0]);
        $this->assertSame('shopper_001', $response->shoppers[0]->id);
        $this->assertNull($response->error);
    }

    public function test_parsesPaginationLinks(): void
    {
        $responseData = [
            'items' => [['id' => 'shopper_001']],
            'next' => 'next_token',
            'first' => 'first_token',
        ];

        $response = new ShopperListResponse($responseData, 200);

        $this->assertSame('next_token', $response->nextPage);
        $this->assertSame('first_token', $response->firstPage);
        $this->assertTrue($response->hasMorePages());
    }

    public function test_handlesEmptyList(): void
    {
        $responseData = [
            'items' => [],
            'next' => null,
            'first' => null,
        ];

        $response = new ShopperListResponse($responseData, 200);

        $this->assertTrue($response->isSuccessful());
        $this->assertCount(0, $response->shoppers);
        $this->assertNull($response->nextPage);
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

        $response = new ShopperListResponse($errorData, 401);

        $this->assertFalse($response->isSuccessful());
        $this->assertNull($response->shoppers);
        $this->assertInstanceOf(ErrorResponse::class, $response->error);
        $this->assertSame(401, $response->error->status);
    }
}
