<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Response\PaymentLink;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentLinkEvent;
use Academe\Elavon\Epg\Psr7\Enums\PaymentLinkEventType;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\PaymentLink\PaymentLinkEventListResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class PaymentLinkEventListResponseTest extends TestCase
{
    public function test_construct_withSuccessResponse_parsesPaymentLinkEvents(): void
    {
        $responseData = [
            'items' => [
                [
                    'id' => 'e1',
                    'type' => 'payment',
                    'createdAt' => '2025-11-19T10:00:00Z',
                ],
                [
                    'id' => 'e2',
                    'type' => 'reminderSent',
                    'shopperEmailAddress' => 'shopper@example.com',
                    'createdAt' => '2025-11-19T11:00:00Z',
                ],
            ],
            'next' => 'https://api.example.com/payment-links/pl123/payment-link-events?offset=2',
            'first' => 'https://api.example.com/payment-links/pl123/payment-link-events',
        ];

        $listResponse = new PaymentLinkEventListResponse($responseData, 200);

        $this->assertTrue($listResponse->isSuccessful());
        $this->assertNull($listResponse->error);
        $this->assertIsArray($listResponse->paymentLinkEvents);
        $this->assertCount(2, $listResponse->paymentLinkEvents);
        $this->assertInstanceOf(PaymentLinkEvent::class, $listResponse->paymentLinkEvents[0]);
        $this->assertSame('e1', $listResponse->paymentLinkEvents[0]->id);
        $this->assertSame(PaymentLinkEventType::PAYMENT, $listResponse->paymentLinkEvents[0]->type);
        $this->assertSame('e2', $listResponse->paymentLinkEvents[1]->id);
        $this->assertSame(PaymentLinkEventType::REMINDER_SENT, $listResponse->paymentLinkEvents[1]->type);
    }

    public function test_construct_withPaginationLinks_parsesPagination(): void
    {
        $responseData = [
            'items' => [],
            'next' => 'https://api.example.com/payment-links/pl123/payment-link-events?offset=50',
            'first' => 'https://api.example.com/payment-links/pl123/payment-link-events',
        ];

        $listResponse = new PaymentLinkEventListResponse($responseData, 200);

        $this->assertSame('https://api.example.com/payment-links/pl123/payment-link-events?offset=50', $listResponse->nextPage);
        $this->assertSame('https://api.example.com/payment-links/pl123/payment-link-events', $listResponse->firstPage);
        $this->assertTrue($listResponse->hasMorePages());
    }

    public function test_construct_withoutNextLink_hasNoMorePages(): void
    {
        $responseData = [
            'items' => [
                [
                    'id' => 'e1',
                    'type' => 'payment',
                    'createdAt' => '2025-11-19T10:00:00Z',
                ],
            ],
        ];

        $listResponse = new PaymentLinkEventListResponse($responseData, 200);

        $this->assertNull($listResponse->nextPage);
        $this->assertFalse($listResponse->hasMorePages());
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

        $listResponse = new PaymentLinkEventListResponse($errorData, 404);

        $this->assertFalse($listResponse->isSuccessful());
        $this->assertNull($listResponse->paymentLinkEvents);
        $this->assertNotNull($listResponse->error);
        $this->assertSame('Payment link not found', $listResponse->error->getMessage());
    }

    public function test_construct_withMissingItemsArray_throwsException(): void
    {
        $responseData = [
            'data' => [],
        ];

        $response = $this->createMockResponse(200, $responseData);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response must contain an "items" array');

        PaymentLinkEventListResponse::fromPsr7Response($response);
    }

    public function test_fromPsr7Response_createsInstance(): void
    {
        $responseData = [
            'items' => [
                [
                    'id' => 'e1',
                    'type' => 'payment',
                    'createdAt' => '2025-11-19T10:00:00Z',
                ],
            ],
        ];

        $response = $this->createMockResponse(200, $responseData);
        $listResponse = PaymentLinkEventListResponse::fromPsr7Response($response);

        $this->assertInstanceOf(PaymentLinkEventListResponse::class, $listResponse);
        $this->assertCount(1, $listResponse->paymentLinkEvents);
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
