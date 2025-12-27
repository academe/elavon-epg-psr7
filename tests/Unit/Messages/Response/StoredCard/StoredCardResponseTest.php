<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Response\StoredCard;

use Academe\Elavon\Epg\Psr7\Dtos\ErrorResponse;
use Academe\Elavon\Epg\Psr7\Dtos\StoredCard;
use Academe\Elavon\Epg\Psr7\Messages\Response\StoredCard\StoredCardResponse;
use Academe\Elavon\Epg\Psr7\Support\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * Tests for StoredCardResponse message.
 */
class StoredCardResponseTest extends TestCase
{
    private function createMockResponse(string $body, int $statusCode): ResponseInterface
    {
        $stream = new Stream($body);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn($statusCode);

        return $response;
    }

    public function test_construct_withSuccessResponse_parsesStoredCard(): void
    {
        // Arrange
        $responseBody = json_encode([
            'id' => 'sc123',
            'href' => 'https://api.example.com/stored-cards/sc123',
            'createdAt' => '2025-01-01T00:00:00Z',
            'merchant' => 'https://api.example.com/merchants/m123',
            'shopper' => 'https://api.example.com/shoppers/s456',
            'card' => [
                'last4' => '1111',
                'bin' => '411111',
                'scheme' => 'Visa',
            ],
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 201);

        // Act
        $response = StoredCardResponse::fromPsr7Response($psrResponse);

        // Assert
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->hasError());
        $this->assertInstanceOf(StoredCard::class, $response->storedCard);
        $this->assertSame('sc123', $response->storedCard->id);
        $this->assertSame('https://api.example.com/shoppers/s456', $response->storedCard->shopper);
    }

    public function test_construct_withErrorResponse_parsesError(): void
    {
        // Arrange
        $responseBody = json_encode([
            'status' => 400,
            'failures' => [
                ['code' => 'invalid_shopper', 'description' => 'Shopper URL is invalid'],
            ],
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 400);

        // Act
        $response = StoredCardResponse::fromPsr7Response($psrResponse);

        // Assert
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->hasError());
        $this->assertNull($response->storedCard);
        $this->assertInstanceOf(ErrorResponse::class, $response->error);
        $this->assertSame(400, $response->error->status);
    }

    public function test_construct_with201StatusCode_isSuccessful(): void
    {
        // Arrange
        $responseBody = json_encode([
            'id' => 'sc456',
            'shopper' => 'https://api.example.com/shoppers/s123',
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 201);

        // Act
        $response = StoredCardResponse::fromPsr7Response($psrResponse);

        // Assert
        $this->assertTrue($response->isSuccessful());
        $this->assertSame(201, $response->statusCode);
    }

    public function test_construct_with200StatusCode_isSuccessful(): void
    {
        // Arrange
        $responseBody = json_encode([
            'id' => 'sc789',
            'shopper' => 'https://api.example.com/shoppers/s456',
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Act
        $response = StoredCardResponse::fromPsr7Response($psrResponse);

        // Assert
        $this->assertTrue($response->isSuccessful());
        $this->assertSame(200, $response->statusCode);
    }

    public function test_getStoredCard_parsesNestedCard(): void
    {
        // Arrange
        $responseBody = json_encode([
            'id' => 'sc999',
            'card' => [
                'last4' => '5555',
                'bin' => '555555',
                'scheme' => 'MasterCard',
                'fingerprint' => 'fp123',
            ],
            'shopper' => 'https://api.example.com/shoppers/s789',
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Act
        $response = StoredCardResponse::fromPsr7Response($psrResponse);

        // Assert
        $storedCard = $response->storedCard;
        $this->assertNotNull($storedCard->card);
        $this->assertSame('5555', $storedCard->card->last4);
        $this->assertSame('555555', $storedCard->card->bin);
        $this->assertSame('https://api.example.com/shoppers/s789', $storedCard->shopper);
    }

    public function test_getStoredCard_parsesCustomFields(): void
    {
        // Arrange
        $responseBody = json_encode([
            'id' => 'sc111',
            'shopper' => 'https://api.example.com/shoppers/s999',
            'customReference' => 'customer-ref-123',
            'customFields' => [
                'subscriptionId' => 'SUB-456',
                'tier' => 'platinum',
            ],
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Act
        $response = StoredCardResponse::fromPsr7Response($psrResponse);

        // Assert
        $storedCard = $response->storedCard;
        $this->assertSame('customer-ref-123', $storedCard->customReference);
        $this->assertSame('SUB-456', $storedCard->customFields['subscriptionId']);
        $this->assertSame('platinum', $storedCard->customFields['tier']);
    }

    public function test_fromPsr7Response_createsInstance(): void
    {
        // Arrange
        $responseBody = json_encode([
            'id' => 'sc222',
            'shopper' => 'https://api.example.com/shoppers/s111',
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Act
        $response = StoredCardResponse::fromPsr7Response($psrResponse);

        // Assert
        $this->assertInstanceOf(StoredCardResponse::class, $response);
        $this->assertSame('sc222', $response->storedCard->id);
    }

    public function test_getStatusCode_returnsCorrectCode(): void
    {
        // Arrange
        $responseBody = json_encode([
            'id' => 'sc333',
            'shopper' => 'https://api.example.com/shoppers/s222',
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 201);

        // Act
        $response = StoredCardResponse::fromPsr7Response($psrResponse);

        // Assert
        $this->assertSame(201, $response->statusCode);
    }
}
