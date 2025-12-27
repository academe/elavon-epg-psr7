<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Response\HostedCard;

use Academe\Elavon\Epg\Psr7\Dtos\ErrorResponse;
use Academe\Elavon\Epg\Psr7\Dtos\HostedCard;
use Academe\Elavon\Epg\Psr7\Messages\Response\HostedCard\HostedCardResponse;
use Academe\Elavon\Epg\Psr7\Support\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * Tests for HostedCardResponse message.
 */
class HostedCardResponseTest extends TestCase
{
    private function createMockResponse(string $body, int $statusCode): ResponseInterface
    {
        $stream = new Stream($body);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn($statusCode);

        return $response;
    }

    public function test_construct_withSuccessResponse_parsesHostedCard(): void
    {
        // Arrange
        $responseBody = json_encode([
            'id' => 'hc123',
            'href' => 'https://api.example.com/hosted-cards/hc123',
            'createdAt' => '2025-01-01T00:00:00Z',
            'expiresAt' => '2025-01-31T23:59:59Z',
            'merchant' => 'https://api.example.com/merchants/m123',
            'card' => [
                'last4' => '1111',
                'bin' => '411111',
                'scheme' => 'Visa',
            ],
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 201);

        // Act
        $response = HostedCardResponse::fromPsr7Response($psrResponse);

        // Assert
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->hasError());
        $this->assertInstanceOf(HostedCard::class, $response->hostedCard);
        $this->assertSame('hc123', $response->hostedCard->id);
        $this->assertSame('2025-01-31T23:59:59Z', $response->hostedCard->expiresAt);
    }

    public function test_construct_withErrorResponse_parsesError(): void
    {
        // Arrange
        $responseBody = json_encode([
            'status' => 400,
            'failures' => [
                ['code' => 'invalid_card', 'description' => 'Card number is invalid'],
            ],
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 400);

        // Act
        $response = HostedCardResponse::fromPsr7Response($psrResponse);

        // Assert
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->hasError());
        $this->assertNull($response->hostedCard);
        $this->assertInstanceOf(ErrorResponse::class, $response->error);
        $this->assertSame(400, $response->error->status);
    }

    public function test_construct_with201StatusCode_isSuccessful(): void
    {
        // Arrange
        $responseBody = json_encode([
            'id' => 'hc456',
            'card' => ['last4' => '4444'],
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 201);

        // Act
        $response = HostedCardResponse::fromPsr7Response($psrResponse);

        // Assert
        $this->assertTrue($response->isSuccessful());
        $this->assertSame(201, $response->statusCode);
    }

    public function test_construct_with200StatusCode_isSuccessful(): void
    {
        // Arrange
        $responseBody = json_encode([
            'id' => 'hc789',
            'card' => ['last4' => '9999'],
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Act
        $response = HostedCardResponse::fromPsr7Response($psrResponse);

        // Assert
        $this->assertTrue($response->isSuccessful());
        $this->assertSame(200, $response->statusCode);
    }

    public function test_getHostedCard_parsesNestedCard(): void
    {
        // Arrange
        $responseBody = json_encode([
            'id' => 'hc999',
            'card' => [
                'last4' => '5555',
                'bin' => '555555',
                'scheme' => 'MasterCard',
                'fingerprint' => 'fp123',
            ],
            'doVerify' => true,
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Act
        $response = HostedCardResponse::fromPsr7Response($psrResponse);

        // Assert
        $hostedCard = $response->hostedCard;
        $this->assertNotNull($hostedCard->card);
        $this->assertSame('5555', $hostedCard->card->last4);
        $this->assertSame('555555', $hostedCard->card->bin);
        $this->assertTrue($hostedCard->doVerify);
    }

    public function test_getHostedCard_parsesCustomFields(): void
    {
        // Arrange
        $responseBody = json_encode([
            'id' => 'hc111',
            'customReference' => 'order-123',
            'customFields' => [
                'orderId' => 'ORD-456',
                'customerId' => 'CUST-789',
            ],
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Act
        $response = HostedCardResponse::fromPsr7Response($psrResponse);

        // Assert
        $hostedCard = $response->hostedCard;
        $this->assertSame('order-123', $hostedCard->customReference);
        $this->assertSame('ORD-456', $hostedCard->customFields['orderId']);
        $this->assertSame('CUST-789', $hostedCard->customFields['customerId']);
    }

    public function test_fromPsr7Response_createsInstance(): void
    {
        // Arrange
        $responseBody = json_encode(['id' => 'hc222']);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Act
        $response = HostedCardResponse::fromPsr7Response($psrResponse);

        // Assert
        $this->assertInstanceOf(HostedCardResponse::class, $response);
        $this->assertSame('hc222', $response->hostedCard->id);
    }

    public function test_getStatusCode_returnsCorrectCode(): void
    {
        // Arrange
        $responseBody = json_encode(['id' => 'hc333']);
        $psrResponse = $this->createMockResponse($responseBody, 201);

        // Act
        $response = HostedCardResponse::fromPsr7Response($psrResponse);

        // Assert
        $this->assertSame(201, $response->statusCode);
    }
}
