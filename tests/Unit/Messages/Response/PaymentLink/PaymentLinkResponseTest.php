<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Response\PaymentLink;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentLink;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\PaymentLink\PaymentLinkResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class PaymentLinkResponseTest extends TestCase
{
    public function test_construct_withSuccessResponse_parsesPaymentLink(): void
    {
        $responseData = [
            'id' => 'pl123',
            'href' => 'https://api.example.com/payment-links/pl123',
            'url' => 'https://hpp.example.com/payment-links/pl123',
            'total' => ['amount' => '100.00', 'currencyCode' => 'USD'],
            'expiresAt' => '2025-12-31T23:59:59Z',
            'description' => 'Test payment link',
            'createdAt' => '2025-11-19T10:00:00Z',
            'status' => ['active'],
        ];

        $paymentLinkResponse = new PaymentLinkResponse($responseData, 200);

        $this->assertTrue($paymentLinkResponse->isSuccessful());
        $this->assertNull($paymentLinkResponse->error);
        $this->assertInstanceOf(PaymentLink::class, $paymentLinkResponse->paymentLink);
        $this->assertSame('pl123', $paymentLinkResponse->paymentLink->id);
        $this->assertSame('10000', $paymentLinkResponse->paymentLink->total->getAmount());
        $this->assertSame('Test payment link', $paymentLinkResponse->paymentLink->description);
    }

    public function test_construct_withErrorResponse_parsesError(): void
    {
        $errorData = [
            'status' => 404,
            'failures' => [
                [
                    'code' => 'PAYMENT_LINK_NOT_FOUND',
                    'description' => 'Payment link not found',
                    'field' => null,
                ],
            ],
        ];

        $paymentLinkResponse = new PaymentLinkResponse($errorData, 404);

        $this->assertFalse($paymentLinkResponse->isSuccessful());
        $this->assertNull($paymentLinkResponse->paymentLink);
        $this->assertNotNull($paymentLinkResponse->error);
        $this->assertSame('Payment link not found', $paymentLinkResponse->error->getMessage());
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

        PaymentLinkResponse::fromPsr7Response($response);
    }

    public function test_fromPsr7Response_createsInstance(): void
    {
        $responseData = [
            'id' => 'pl456',
            'total' => ['amount' => '250.00', 'currencyCode' => 'EUR'],
            'expiresAt' => '2025-12-31T23:59:59Z',
        ];

        $response = $this->createMockResponse(200, $responseData);
        $paymentLinkResponse = PaymentLinkResponse::fromPsr7Response($response);

        $this->assertInstanceOf(PaymentLinkResponse::class, $paymentLinkResponse);
        $this->assertSame('pl456', $paymentLinkResponse->paymentLink->id);
    }

    public function test_getStatusCode_returnsStatusCode(): void
    {
        $responseData = [
            'id' => 'pl789',
            'total' => ['amount' => '50.00', 'currencyCode' => 'USD'],
            'expiresAt' => '2025-12-31T23:59:59Z',
        ];

        $paymentLinkResponse = new PaymentLinkResponse($responseData, 201);

        $this->assertSame(201, $paymentLinkResponse->statusCode);
    }
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
