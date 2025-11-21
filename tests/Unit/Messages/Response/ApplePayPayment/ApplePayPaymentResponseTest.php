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

        $psr7Response = $this->createMockResponse(json_encode($responseData), 201);
        $response = new ApplePayPaymentResponse($psr7Response);

        $this->assertTrue($response->isSuccessful());
        $this->assertInstanceOf(ApplePayPayment::class, $response->getApplePayPayment());
        $this->assertSame('payment_123', $response->getApplePayPayment()->id);
        $this->assertNull($response->getError());
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
        $response = new ApplePayPaymentResponse($psr7Response);

        $this->assertFalse($response->isSuccessful());
        $this->assertNull($response->getApplePayPayment());
        $this->assertInstanceOf(ErrorResponse::class, $response->getError());
        $this->assertSame(401, $response->getError()->status);
    }

    public function test_fromPsr7Response_factoryMethod(): void
    {
        $responseData = ['id' => 'payment_456'];

        $psr7Response = $this->createMockResponse(json_encode($responseData), 200);
        $response = ApplePayPaymentResponse::fromPsr7Response($psr7Response);

        $this->assertInstanceOf(ApplePayPaymentResponse::class, $response);
        $this->assertSame('payment_456', $response->getApplePayPayment()->id);
    }

    public function test_getStatusCode_returnsCorrectCode(): void
    {
        $responseData = ['id' => 'payment_789'];

        $psr7Response = $this->createMockResponse(json_encode($responseData), 201);
        $response = new ApplePayPaymentResponse($psr7Response);

        $this->assertSame(201, $response->getStatusCode());
    }

    public function test_getPsr7Response_returnsOriginalResponse(): void
    {
        $responseData = ['id' => 'payment_000'];

        $psr7Response = $this->createMockResponse(json_encode($responseData), 200);
        $response = new ApplePayPaymentResponse($psr7Response);

        $this->assertSame($psr7Response, $response->getPsr7Response());
    }
}
