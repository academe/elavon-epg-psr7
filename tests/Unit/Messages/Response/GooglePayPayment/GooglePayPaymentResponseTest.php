<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Response\GooglePayPayment;

use Academe\Elavon\Epg\Psr7\Dtos\GooglePayPayment;
use Academe\Elavon\Epg\Psr7\Dtos\ErrorResponse;
use Academe\Elavon\Epg\Psr7\Messages\Response\GooglePayPayment\GooglePayPaymentResponse;
use Academe\Elavon\Epg\Psr7\Support\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class GooglePayPaymentResponseTest extends TestCase
{
    private function createMockResponse(string $body, int $statusCode): ResponseInterface
    {
        $stream = new Stream($body);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn($statusCode);

        return $response;
    }

    public function test_construct_withSuccessResponse_parsesGooglePayPayment(): void
    {
        $responseData = [
            'id' => 'payment_123',
            'href' => 'https://api.example.com/google-pay-payments/payment_123',
            'createdAt' => '2025-01-01T00:00:00Z',
            'customReference' => 'ref123',
        ];

        $response = new GooglePayPaymentResponse($responseData, 201);

        $this->assertTrue($response->isSuccessful());
        $this->assertInstanceOf(GooglePayPayment::class, $response->googlePayPayment);
        $this->assertSame('payment_123', $response->googlePayPayment->id);
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

        $response = new GooglePayPaymentResponse($errorData, 401);

        $this->assertFalse($response->isSuccessful());
        $this->assertNull($response->googlePayPayment);
        $this->assertInstanceOf(ErrorResponse::class, $response->error);
        $this->assertSame(401, $response->error->status);
    }

    public function test_fromPsr7Response_factoryMethod(): void
    {
        $responseData = ['id' => 'payment_456'];

        $psr7Response = $this->createMockResponse(json_encode($responseData), 200);
        $response = GooglePayPaymentResponse::fromPsr7Response($psr7Response);

        $this->assertInstanceOf(GooglePayPaymentResponse::class, $response);
        $this->assertSame('payment_456', $response->googlePayPayment->id);
    }

    public function test_getStatusCode_returnsCorrectCode(): void
    {
        $responseData = ['id' => 'payment_789'];

        $response = new GooglePayPaymentResponse($responseData, 201);

        $this->assertSame(201, $response->statusCode);
    }
}
