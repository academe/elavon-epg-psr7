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

        $listResponse = new PaymentLinkListResponse($responseData, 200);

        $this->assertTrue($listResponse->isSuccessful());
        $this->assertNull($listResponse->error);
        $this->assertIsArray($listResponse->paymentLinks);
        $this->assertCount(2, $listResponse->paymentLinks);
        $this->assertInstanceOf(PaymentLink::class, $listResponse->paymentLinks[0]);
        $this->assertSame('pl1', $listResponse->paymentLinks[0]->id);
        $this->assertSame('pl2', $listResponse->paymentLinks[1]->id);
    }

    public function test_construct_withPaginationLinks_parsesPagination(): void
    {
        $responseData = [
            'items' => [],
            'next' => 'https://api.example.com/payment-links?offset=50',
            'first' => 'https://api.example.com/payment-links',
        ];

        $listResponse = new PaymentLinkListResponse($responseData, 200);

        $this->assertSame('https://api.example.com/payment-links?offset=50', $listResponse->nextPage);
        $this->assertSame('https://api.example.com/payment-links', $listResponse->firstPage);
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

        $listResponse = new PaymentLinkListResponse($responseData, 200);

        $this->assertNull($listResponse->nextPage);
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

        $listResponse = new PaymentLinkListResponse($errorData, 400);

        $this->assertFalse($listResponse->isSuccessful());
        $this->assertNull($listResponse->paymentLinks);
        $this->assertNotNull($listResponse->error);
        $this->assertSame('Invalid request parameters', $listResponse->error->getMessage());
    }

    public function test_construct_withMissingItemsArray_throwsException(): void
    {
        $responseData = [
            'data' => [],
        ];

        $response = $this->createMockResponse(200, $responseData);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response must contain an "items" array');

        PaymentLinkListResponse::fromPsr7Response($response);
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
        $this->assertCount(1, $listResponse->paymentLinks);
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
