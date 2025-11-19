<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Response;

use Academe\Elavon\Epg\Psr7\Dtos\ErrorResponse;
use Academe\Elavon\Epg\Psr7\Dtos\Shopper;
use Academe\Elavon\Epg\Psr7\Messages\Response\ShopperResponse;
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

        $psr7Response = $this->createMockResponse(json_encode($responseData), 201);
        $response = new ShopperResponse($psr7Response);

        $this->assertTrue($response->isSuccessful());
        $this->assertInstanceOf(Shopper::class, $response->getShopper());
        $this->assertSame('shopper_123', $response->getShopper()->id);
        $this->assertNull($response->getError());
    }

    public function test_construct_withErrorResponse_parsesError(): void
    {
        $errorData = [
            'status' => 404,
            'failures' => [
                ['code' => 'not_found', 'description' => 'Shopper not found'],
            ],
        ];

        $psr7Response = $this->createMockResponse(json_encode($errorData), 404);
        $response = new ShopperResponse($psr7Response);

        $this->assertFalse($response->isSuccessful());
        $this->assertNull($response->getShopper());
        $this->assertInstanceOf(ErrorResponse::class, $response->getError());
        $this->assertSame(404, $response->getError()->status);
    }

    public function test_fromPsr7Response_factoryMethod(): void
    {
        $responseData = ['id' => 'shopper_456', 'fullName' => 'Jane Doe'];

        $psr7Response = $this->createMockResponse(json_encode($responseData), 200);
        $response = ShopperResponse::fromPsr7Response($psr7Response);

        $this->assertInstanceOf(ShopperResponse::class, $response);
        $this->assertSame('shopper_456', $response->getShopper()->id);
    }

    public function test_getStatusCode_returnsCorrectCode(): void
    {
        $responseData = ['id' => 'shopper_789'];

        $psr7Response = $this->createMockResponse(json_encode($responseData), 200);
        $response = new ShopperResponse($psr7Response);

        $this->assertSame(200, $response->getStatusCode());
    }
}
