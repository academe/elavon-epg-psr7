<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Response\PaymentLink;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentLink;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\PaymentLink\PaymentLinkListResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class PaymentLinkListResponseTest extends TestCase
{
    public function test_construct_withSuccessResponse_parsesPaymentLinks(): void
    {
        $responseData = [
            'items' => [
                [
                    'id' => 'pl1',
                    'total' => ['amount' => '100.00', 'currencyCode' => 'USD'],
                    'expiresAt' => '2025-12-31T23:59:59Z',
                ],
                [
                    'id' => 'pl2',
                    'total' => ['amount' => '200.00', 'currencyCode' => 'EUR'],
                    'expiresAt' => '2025-12-31T23:59:59Z',
                ],
            ],
            'next' => 'https://api.example.com/payment-links?offset=2',
            'first' => 'https://api.example.com/payment-links',
        ];

        $response = $this->createMockResponse(200, $responseData);
        $listResponse = new PaymentLinkListResponse($response);

        $this->assertTrue($listResponse->isSuccessful());
        $this->assertNull($listResponse->getError());
        $this->assertIsArray($listResponse->getPaymentLinks());
        $this->assertCount(2, $listResponse->getPaymentLinks());
        $this->assertInstanceOf(PaymentLink::class, $listResponse->getPaymentLinks()[0]);
        $this->assertSame('pl1', $listResponse->getPaymentLinks()[0]->id);
        $this->assertSame('pl2', $listResponse->getPaymentLinks()[1]->id);
    }

    public function test_construct_withPaginationLinks_parsesPagination(): void
    {
        $responseData = [
            'items' => [],
            'next' => 'https://api.example.com/payment-links?offset=50',
            'first' => 'https://api.example.com/payment-links',
        ];

        $response = $this->createMockResponse(200, $responseData);
        $listResponse = new PaymentLinkListResponse($response);

        $this->assertSame('https://api.example.com/payment-links?offset=50', $listResponse->getNext());
        $this->assertSame('https://api.example.com/payment-links', $listResponse->getFirst());
        $this->assertTrue($listResponse->hasMorePages());
    }

    public function test_construct_withoutNextLink_hasNoMorePages(): void
    {
        $responseData = [
            'items' => [
                [
                    'id' => 'pl1',
                    'total' => ['amount' => '100.00', 'currencyCode' => 'USD'],
                    'expiresAt' => '2025-12-31T23:59:59Z',
                ],
            ],
        ];

        $response = $this->createMockResponse(200, $responseData);
        $listResponse = new PaymentLinkListResponse($response);

        $this->assertNull($listResponse->getNext());
        $this->assertFalse($listResponse->hasMorePages());
    }

    public function test_construct_withErrorResponse_parsesError(): void
    {
        $errorData = [
            'status' => 400,
            'failures' => [
                [
                    'code' => 'INVALID_REQUEST',
                    'description' => 'Invalid request parameters',
                    'field' => null,
                ],
            ],
        ];

        $response = $this->createMockResponse(400, $errorData);
        $listResponse = new PaymentLinkListResponse($response);

        $this->assertFalse($listResponse->isSuccessful());
        $this->assertNull($listResponse->getPaymentLinks());
        $this->assertNotNull($listResponse->getError());
        $this->assertSame('Invalid request parameters', $listResponse->getError()->getMessage());
    }

    public function test_construct_withMissingItemsArray_throwsException(): void
    {
        $responseData = [
            'data' => [],
        ];

        $response = $this->createMockResponse(200, $responseData);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response must contain an "items" array');

        new PaymentLinkListResponse($response);
    }

    public function test_fromPsr7Response_createsInstance(): void
    {
        $responseData = [
            'items' => [
                [
                    'id' => 'pl1',
                    'total' => ['amount' => '100.00', 'currencyCode' => 'USD'],
                    'expiresAt' => '2025-12-31T23:59:59Z',
                ],
            ],
        ];

        $response = $this->createMockResponse(200, $responseData);
        $listResponse = PaymentLinkListResponse::fromPsr7Response($response);

        $this->assertInstanceOf(PaymentLinkListResponse::class, $listResponse);
        $this->assertCount(1, $listResponse->getPaymentLinks());
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
