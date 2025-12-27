<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Response\StoredCard;

use Academe\Elavon\Epg\Psr7\Dtos\ErrorResponse;
use Academe\Elavon\Epg\Psr7\Dtos\StoredCard;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\StoredCard\StoredCardListResponse;
use Academe\Elavon\Epg\Psr7\Support\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * Tests for StoredCardListResponse message.
 */
class StoredCardListResponseTest extends TestCase
{
    private function createMockResponse(string $body, int $statusCode): ResponseInterface
    {
        $stream = new Stream($body);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn($statusCode);

        return $response;
    }

    public function test_construct_withSuccessResponse_parsesStoredCards(): void
    {
        // Arrange
        $responseBody = json_encode([
            'items' => [
                [
                    'id' => 'sc1',
                    'createdAt' => '2025-01-01T00:00:00Z',
                    'shopper' => 'https://api.example.com/shoppers/s1',
                ],
                [
                    'id' => 'sc2',
                    'createdAt' => '2025-01-02T00:00:00Z',
                    'shopper' => 'https://api.example.com/shoppers/s2',
                ],
            ],
            'next' => 'https://api.example.com/stored-cards?page=2',
            'first' => 'https://api.example.com/stored-cards?page=1',
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Act
        $response = StoredCardListResponse::fromPsr7Response($psrResponse);

        // Assert
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->hasError());
        $this->assertCount(2, $response->storedCards);
        $this->assertInstanceOf(StoredCard::class, $response->storedCards[0]);
        $this->assertSame('sc1', $response->storedCards[0]->id);
        $this->assertSame('sc2', $response->storedCards[1]->id);
    }

    public function test_construct_withEmptyList_returnsEmptyArray(): void
    {
        // Arrange
        $responseBody = json_encode([
            'items' => [],
            'next' => null,
            'first' => 'https://api.example.com/stored-cards?page=1',
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Act
        $response = StoredCardListResponse::fromPsr7Response($psrResponse);

        // Assert
        $this->assertIsArray($response->storedCards);
        $this->assertCount(0, $response->storedCards);
        $this->assertFalse($response->hasMorePages());
    }

    public function test_getNextPage_withMorePages_returnsUrl(): void
    {
        // Arrange
        $responseBody = json_encode([
            'items' => [['id' => 'sc1', 'shopper' => 'https://api.example.com/shoppers/s1']],
            'next' => 'https://api.example.com/stored-cards?page=2',
            'first' => 'https://api.example.com/stored-cards?page=1',
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Act
        $response = StoredCardListResponse::fromPsr7Response($psrResponse);

        // Assert
        $this->assertSame('https://api.example.com/stored-cards?page=2', $response->nextPage);
        $this->assertTrue($response->hasMorePages());
    }

    public function test_getNextPage_withLastPage_returnsNull(): void
    {
        // Arrange
        $responseBody = json_encode([
            'items' => [['id' => 'sc1', 'shopper' => 'https://api.example.com/shoppers/s1']],
            'next' => null,
            'first' => 'https://api.example.com/stored-cards?page=1',
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Act
        $response = StoredCardListResponse::fromPsr7Response($psrResponse);

        // Assert
        $this->assertNull($response->nextPage);
        $this->assertFalse($response->hasMorePages());
    }

    public function test_getFirstPage_returnsCorrectUrl(): void
    {
        // Arrange
        $responseBody = json_encode([
            'items' => [['id' => 'sc1', 'shopper' => 'https://api.example.com/shoppers/s1']],
            'next' => null,
            'first' => 'https://api.example.com/stored-cards?page=1',
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Act
        $response = StoredCardListResponse::fromPsr7Response($psrResponse);

        // Assert
        $this->assertSame('https://api.example.com/stored-cards?page=1', $response->firstPage);
    }

    public function test_construct_withErrorResponse_parsesError(): void
    {
        // Arrange
        $responseBody = json_encode([
            'status' => 401,
            'failures' => [
                ['code' => 'unauthorized', 'description' => 'Invalid API key'],
            ],
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 401);

        // Act
        $response = StoredCardListResponse::fromPsr7Response($psrResponse);

        // Assert
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->hasError());
        $this->assertNull($response->storedCards);
        $this->assertInstanceOf(ErrorResponse::class, $response->error);
    }

    public function test_construct_withMissingItems_throwsException(): void
    {
        // Arrange
        $responseBody = json_encode([
            'next' => null,
            'first' => 'https://api.example.com/stored-cards?page=1',
            // Missing 'items' array
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response must contain an "items" array');

        // Act
        StoredCardListResponse::fromPsr7Response($psrResponse);
    }

    public function test_construct_withNonArrayItems_throwsException(): void
    {
        // Arrange
        $responseBody = json_encode([
            'items' => 'not an array',
            'next' => null,
            'first' => 'https://api.example.com/stored-cards?page=1',
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response must contain an "items" array');

        // Act
        StoredCardListResponse::fromPsr7Response($psrResponse);
    }

    public function test_construct_withInvalidItemFormat_throwsException(): void
    {
        // Arrange
        $responseBody = json_encode([
            'items' => [
                ['id' => 'sc1', 'shopper' => 'https://api.example.com/shoppers/s1'],
                'invalid item', // Not an array
            ],
            'next' => null,
            'first' => 'https://api.example.com/stored-cards?page=1',
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Item at index 1 is not an array');

        // Act
        StoredCardListResponse::fromPsr7Response($psrResponse);
    }

    public function test_construct_withEmptyBody_throwsException(): void
    {
        // Arrange
        $psrResponse = $this->createMockResponse('', 200);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response body is empty');

        // Act
        StoredCardListResponse::fromPsr7Response($psrResponse);
    }

    public function test_construct_withInvalidJson_throwsException(): void
    {
        // Arrange
        $psrResponse = $this->createMockResponse('invalid json', 200);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed to decode JSON response');

        // Act
        StoredCardListResponse::fromPsr7Response($psrResponse);
    }

    public function test_fromPsr7Response_createsInstance(): void
    {
        // Arrange
        $responseBody = json_encode([
            'items' => [['id' => 'sc1', 'shopper' => 'https://api.example.com/shoppers/s1']],
            'next' => null,
            'first' => 'https://api.example.com/stored-cards?page=1',
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Act
        $response = StoredCardListResponse::fromPsr7Response($psrResponse);

        // Assert
        $this->assertInstanceOf(StoredCardListResponse::class, $response);
        $this->assertCount(1, $response->storedCards);
    }

    public function test_getStatusCode_returnsCorrectCode(): void
    {
        // Arrange
        $responseBody = json_encode([
            'items' => [['id' => 'sc1', 'shopper' => 'https://api.example.com/shoppers/s1']],
            'next' => null,
            'first' => 'https://api.example.com/stored-cards?page=1',
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Act
        $response = StoredCardListResponse::fromPsr7Response($psrResponse);

        // Assert
        $this->assertSame(200, $response->statusCode);
    }
    public function test_construct_withoutPaginationLinks_setsThemToNull(): void
    {
        // Arrange
        $responseBody = json_encode([
            'items' => [['id' => 'sc1', 'shopper' => 'https://api.example.com/shoppers/s1']],
            // No 'next' or 'first' in response
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Act
        $response = StoredCardListResponse::fromPsr7Response($psrResponse);

        // Assert
        $this->assertNull($response->nextPage);
        $this->assertNull($response->firstPage);
        $this->assertFalse($response->hasMorePages());
    }
}
