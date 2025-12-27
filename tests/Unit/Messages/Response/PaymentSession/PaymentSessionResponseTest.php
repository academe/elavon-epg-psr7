<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Response\PaymentSession;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentSession;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\PaymentSession\PaymentSessionResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class PaymentSessionResponseTest extends TestCase
{
    public function test_construct_withSuccessResponse_parsesPaymentSession(): void
    {
        $responseData = [
            'id' => 'ps123',
            'href' => 'https://api.example.com/payment-sessions/ps123',
            'order' => 'https://api.example.com/orders/ord123',
            'url' => 'https://hpp.example.com/ps123',
            'createdAt' => '2025-11-19T10:00:00Z',
            'modifiedAt' => '2025-11-19T11:00:00Z',
            'hppType' => 'fullPageRedirect',
            'returnUrl' => 'https://merchant.com/return',
            'doCreateTransaction' => true,
        ];

        $paymentSessionResponse = new PaymentSessionResponse($responseData, 200);

        $this->assertTrue($paymentSessionResponse->isSuccessful());
        $this->assertNull($paymentSessionResponse->error);
        $this->assertInstanceOf(PaymentSession::class, $paymentSessionResponse->paymentSession);
        $this->assertSame('ps123', $paymentSessionResponse->paymentSession->id);
        $this->assertSame('https://api.example.com/orders/ord123', $paymentSessionResponse->paymentSession->order);
        $this->assertSame('https://merchant.com/return', $paymentSessionResponse->paymentSession->returnUrl);
    }

    public function test_construct_withSuccessResponseAndContacts_parsesPaymentSession(): void
    {
        $responseData = [
            'id' => 'ps456',
            'order' => 'https://api.example.com/orders/ord456',
            'billTo' => [
                'fullName' => 'John Doe',
                'street1' => '123 Main St',
                'city' => 'New York',
            ],
            'shipTo' => [
                'fullName' => 'Jane Smith',
                'street1' => '456 Oak Ave',
                'city' => 'Boston',
            ],
            'salesTax' => [
                'amount' => '10.50',
                'currencyCode' => 'USD',
            ],
        ];

        $paymentSessionResponse = new PaymentSessionResponse($responseData, 200);

        $this->assertTrue($paymentSessionResponse->isSuccessful());
        $paymentSession = $paymentSessionResponse->paymentSession;
        $this->assertNotNull($paymentSession->billTo);
        $this->assertSame('John Doe', $paymentSession->billTo->fullName);
        $this->assertNotNull($paymentSession->shipTo);
        $this->assertSame('Jane Smith', $paymentSession->shipTo->fullName);
        $this->assertNotNull($paymentSession->salesTax);
        $this->assertSame('1050', $paymentSession->salesTax->getAmount());
    }

    public function test_construct_withSuccessResponseAndPaymentMethods_parsesPaymentSession(): void
    {
        $responseData = [
            'id' => 'ps789',
            'order' => 'https://api.example.com/orders/ord789',
            'allowedPaymentMethods' => ['Card', 'BLIK'],
            'allowedPaymentMethodOrigins' => ['Card', 'Apple Pay', 'Google Pay'],
        ];

        $paymentSessionResponse = new PaymentSessionResponse($responseData, 201);

        $this->assertTrue($paymentSessionResponse->isSuccessful());
        $paymentSession = $paymentSessionResponse->paymentSession;
        $this->assertIsArray($paymentSession->allowedPaymentMethods);
        $this->assertCount(2, $paymentSession->allowedPaymentMethods);
        $this->assertIsArray($paymentSession->allowedPaymentMethodOrigins);
        $this->assertCount(3, $paymentSession->allowedPaymentMethodOrigins);
    }

    public function test_construct_withErrorResponse_parsesError(): void
    {
        $errorData = [
            'status' => 404,
            'failures' => [
                [
                    'code' => 'PAYMENT_SESSION_NOT_FOUND',
                    'description' => 'Payment session not found',
                    'field' => null,
                ],
            ],
        ];

        $paymentSessionResponse = new PaymentSessionResponse($errorData, 404);

        $this->assertFalse($paymentSessionResponse->isSuccessful());
        $this->assertNull($paymentSessionResponse->paymentSession);
        $this->assertNotNull($paymentSessionResponse->error);
        $this->assertSame('Payment session not found', $paymentSessionResponse->error->getMessage());
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

        PaymentSessionResponse::fromPsr7Response($response);
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

        PaymentSessionResponse::fromPsr7Response($response);
    }

    public function test_construct_withJsonArray_throwsException(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $stream->method('__toString')->willReturn('[]');
        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn(200);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response body is not a JSON object');

        PaymentSessionResponse::fromPsr7Response($response);
    }

    public function test_fromPsr7Response_createsInstance(): void
    {
        $responseData = [
            'id' => 'ps999',
            'order' => 'https://api.example.com/orders/ord999',
        ];

        $response = $this->createMockResponse(200, $responseData);
        $paymentSessionResponse = PaymentSessionResponse::fromPsr7Response($response);

        $this->assertInstanceOf(PaymentSessionResponse::class, $paymentSessionResponse);
        $this->assertSame('ps999', $paymentSessionResponse->paymentSession->id);
    }

    public function test_getStatusCode_returnsCorrectCode(): void
    {
        $responseData = [
            'id' => 'ps111',
            'order' => 'https://api.example.com/orders/ord111',
        ];

        $paymentSessionResponse = new PaymentSessionResponse($responseData, 201);

        $this->assertSame(201, $paymentSessionResponse->statusCode);
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
