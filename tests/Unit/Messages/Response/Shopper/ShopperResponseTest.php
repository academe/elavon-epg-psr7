<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Response\Shopper;

use Academe\Elavon\Epg\Psr7\Dtos\ErrorResponse;
use Academe\Elavon\Epg\Psr7\Dtos\Shopper;
use Academe\Elavon\Epg\Psr7\Messages\Response\Shopper\ShopperResponse;
use Academe\Elavon\Epg\Psr7\Support\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ShopperResponseTest extends TestCase
{
    private function createMockResponse(string $body, int $statusCode): ResponseInterface
    {
        $stream = new Stream($body);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn($statusCode);

        return $response;
    }

    public function test_construct_withSuccessResponse_parsesShopper(): void
    {
        $responseData = [
            'id' => 'shopper_123',
            'fullName' => 'John Doe',
            'email' => 'john@example.com',
        ];

        $response = new ShopperResponse($responseData, 201);

        $this->assertTrue($response->isSuccessful());
        $this->assertInstanceOf(Shopper::class, $response->shopper);
        $this->assertSame('shopper_123', $response->shopper->id);
        $this->assertNull($response->error);
    }

    public function test_construct_withErrorResponse_parsesError(): void
    {
        $errorData = [
            'status' => 404,
            'failures' => [
                ['code' => 'not_found', 'description' => 'Shopper not found'],
            ],
        ];

        $response = new ShopperResponse($errorData, 404);

        $this->assertFalse($response->isSuccessful());
        $this->assertNull($response->shopper);
        $this->assertInstanceOf(ErrorResponse::class, $response->error);
        $this->assertSame(404, $response->error->status);
    }

    public function test_fromPsr7Response_factoryMethod(): void
    {
        $responseData = ['id' => 'shopper_456', 'fullName' => 'Jane Doe'];

        $psr7Response = $this->createMockResponse(json_encode($responseData), 200);
        $response = ShopperResponse::fromPsr7Response($psr7Response);

        $this->assertInstanceOf(ShopperResponse::class, $response);
        $this->assertSame('shopper_456', $response->shopper->id);
    }

    public function test_getStatusCode_returnsCorrectCode(): void
    {
        $responseData = ['id' => 'shopper_789'];

        $response = new ShopperResponse($responseData, 200);

        $this->assertSame(200, $response->statusCode);
    }
}
