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

        $response = $this->createMockResponse(200, $responseData);
        $paymentLinkResponse = new PaymentLinkResponse($response);

        $this->assertTrue($paymentLinkResponse->isSuccessful());
        $this->assertNull($paymentLinkResponse->getError());
        $this->assertInstanceOf(PaymentLink::class, $paymentLinkResponse->getPaymentLink());
        $this->assertSame('pl123', $paymentLinkResponse->getPaymentLink()->id);
        $this->assertSame('100.00', $paymentLinkResponse->getPaymentLink()->total->amount);
        $this->assertSame('Test payment link', $paymentLinkResponse->getPaymentLink()->description);
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

        $response = $this->createMockResponse(404, $errorData);
        $paymentLinkResponse = new PaymentLinkResponse($response);

        $this->assertFalse($paymentLinkResponse->isSuccessful());
        $this->assertNull($paymentLinkResponse->getPaymentLink());
        $this->assertNotNull($paymentLinkResponse->getError());
        $this->assertSame('Payment link not found', $paymentLinkResponse->getError()->getMessage());
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

        new PaymentLinkResponse($response);
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
        $this->assertSame('pl456', $paymentLinkResponse->getPaymentLink()->id);
    }

    public function test_getStatusCode_returnsStatusCode(): void
    {
        $responseData = [
            'id' => 'pl789',
            'total' => ['amount' => '50.00', 'currencyCode' => 'USD'],
            'expiresAt' => '2025-12-31T23:59:59Z',
        ];

        $response = $this->createMockResponse(201, $responseData);
        $paymentLinkResponse = new PaymentLinkResponse($response);

        $this->assertSame(201, $paymentLinkResponse->getStatusCode());
    }

    public function test_getPsr7Response_returnsOriginalResponse(): void
    {
        $responseData = [
            'id' => 'pl999',
            'total' => ['amount' => '75.00', 'currencyCode' => 'USD'],
            'expiresAt' => '2025-12-31T23:59:59Z',
        ];

        $response = $this->createMockResponse(200, $responseData);
        $paymentLinkResponse = new PaymentLinkResponse($response);

        $this->assertSame($response, $paymentLinkResponse->getPsr7Response());
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
