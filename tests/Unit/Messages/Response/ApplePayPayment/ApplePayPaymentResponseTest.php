<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Response\ApplePayPayment;

use Academe\Elavon\Epg\Psr7\Dtos\ApplePayPayment;
use Academe\Elavon\Epg\Psr7\Dtos\ErrorResponse;
use Academe\Elavon\Epg\Psr7\Messages\Response\ApplePayPayment\ApplePayPaymentResponse;
use Academe\Elavon\Epg\Psr7\Support\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ApplePayPaymentResponseTest extends TestCase
{
    private function createMockResponse(string $body, int $statusCode): ResponseInterface
    {
        $stream = new Stream($body);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn($statusCode);

        return $response;
    }

    public function test_construct_withSuccessResponse_parsesApplePayPayment(): void
    {
        $responseData = [
            'id' => 'payment_123',
            'href' => 'https://api.example.com/apple-pay-payments/payment_123',
            'createdAt' => '2025-01-01T00:00:00Z',
            'customReference' => 'ref123',
        ];

        $response = new ApplePayPaymentResponse($responseData, 201);

        $this->assertTrue($response->isSuccessful());
        $this->assertInstanceOf(ApplePayPayment::class, $response->applePayPayment);
        $this->assertSame('payment_123', $response->applePayPayment->id);
        $this->assertNull($response->error);
    }

    public function test_construct_withErrorResponse_parsesError(): void
    {
        $errorData = [
            'status' => 401,
            'failures' => [
                ['code' => 'unauthorized', 'description' => 'Unauthorized'],
            ],
        ];

        $response = new ApplePayPaymentResponse($errorData, 401);

        $this->assertFalse($response->isSuccessful());
        $this->assertNull($response->applePayPayment);
        $this->assertInstanceOf(ErrorResponse::class, $response->error);
        $this->assertSame(401, $response->error->status);
    }

    public function test_fromPsr7Response_factoryMethod(): void
    {
        $responseData = ['id' => 'payment_456'];

        $psr7Response = $this->createMockResponse(json_encode($responseData), 200);
        $response = ApplePayPaymentResponse::fromPsr7Response($psr7Response);

        $this->assertInstanceOf(ApplePayPaymentResponse::class, $response);
        $this->assertSame('payment_456', $response->applePayPayment->id);
    }

    public function test_getStatusCode_returnsCorrectCode(): void
    {
        $responseData = ['id' => 'payment_789'];

        $response = new ApplePayPaymentResponse($responseData, 201);

        $this->assertSame(201, $response->statusCode);
    }
}
