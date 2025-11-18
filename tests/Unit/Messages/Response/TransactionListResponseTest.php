<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Response;

use Academe\Elavon\Epg\Psr7\Dtos\ErrorResponse;
use Academe\Elavon\Epg\Psr7\Dtos\Transaction;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\TransactionListResponse;
use Academe\Elavon\Epg\Psr7\Support\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * Tests for TransactionListResponse message.
 */
class TransactionListResponseTest extends TestCase
{
    private function createMockResponse(string $body, int $statusCode): ResponseInterface
    {
        $stream = new Stream($body);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn($statusCode);

        return $response;
    }

    public function test_construct_withSuccessResponse_parsesTransactions(): void
    {
        // Arrange
        $responseBody = json_encode([
            'items' => [
                ['id' => 'txn1', 'description' => 'Transaction 1'],
                ['id' => 'txn2', 'description' => 'Transaction 2'],
            ],
            'next' => 'https://api.example.com/transactions?page=2',
            'first' => 'https://api.example.com/transactions?page=1',
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Act
        $response = new TransactionListResponse($psrResponse);

        // Assert
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->hasError());
        $this->assertCount(2, $response->getTransactions());
        $this->assertInstanceOf(Transaction::class, $response->getTransactions()[0]);
        $this->assertSame('txn1', $response->getTransactions()[0]->id);
        $this->assertSame('txn2', $response->getTransactions()[1]->id);
    }

    public function test_construct_withEmptyList_returnsEmptyArray(): void
    {
        // Arrange
        $responseBody = json_encode([
            'items' => [],
            'next' => null,
            'first' => 'https://api.example.com/transactions?page=1',
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Act
        $response = new TransactionListResponse($psrResponse);

        // Assert
        $this->assertIsArray($response->getTransactions());
        $this->assertCount(0, $response->getTransactions());
        $this->assertFalse($response->hasMorePages());
    }

    public function test_getNextPage_withMorePages_returnsUrl(): void
    {
        // Arrange
        $responseBody = json_encode([
            'items' => [['id' => 'txn1']],
            'next' => 'https://api.example.com/transactions?page=2',
            'first' => 'https://api.example.com/transactions?page=1',
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Act
        $response = new TransactionListResponse($psrResponse);

        // Assert
        $this->assertSame('https://api.example.com/transactions?page=2', $response->getNextPage());
        $this->assertTrue($response->hasMorePages());
    }

    public function test_getNextPage_withLastPage_returnsNull(): void
    {
        // Arrange
        $responseBody = json_encode([
            'items' => [['id' => 'txn1']],
            'next' => null,
            'first' => 'https://api.example.com/transactions?page=1',
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Act
        $response = new TransactionListResponse($psrResponse);

        // Assert
        $this->assertNull($response->getNextPage());
        $this->assertFalse($response->hasMorePages());
    }

    public function test_getFirstPage_returnsCorrectUrl(): void
    {
        // Arrange
        $responseBody = json_encode([
            'items' => [['id' => 'txn1']],
            'next' => null,
            'first' => 'https://api.example.com/transactions?page=1',
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Act
        $response = new TransactionListResponse($psrResponse);

        // Assert
        $this->assertSame('https://api.example.com/transactions?page=1', $response->getFirstPage());
    }

    public function test_construct_withErrorResponse_parsesError(): void
    {
        // Arrange
        $responseBody = json_encode([
            'message' => 'Unauthorized',
            'failures' => [
                ['code' => 'unauthorized', 'description' => 'Invalid API key'],
            ],
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 401);

        // Act
        $response = new TransactionListResponse($psrResponse);

        // Assert
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->hasError());
        $this->assertNull($response->getTransactions());
        $this->assertInstanceOf(ErrorResponse::class, $response->getError());
    }

    public function test_construct_withMissingItems_throwsException(): void
    {
        // Arrange
        $responseBody = json_encode([
            'next' => null,
            'first' => 'https://api.example.com/transactions?page=1',
            // Missing 'items' array
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response must contain an "items" array');

        // Act
        new TransactionListResponse($psrResponse);
    }

    public function test_construct_withNonArrayItems_throwsException(): void
    {
        // Arrange
        $responseBody = json_encode([
            'items' => 'not an array',
            'next' => null,
            'first' => 'https://api.example.com/transactions?page=1',
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response must contain an "items" array');

        // Act
        new TransactionListResponse($psrResponse);
    }

    public function test_construct_withInvalidItemFormat_throwsException(): void
    {
        // Arrange
        $responseBody = json_encode([
            'items' => [
                ['id' => 'txn1'],
                'invalid item', // Not an array
            ],
            'next' => null,
            'first' => 'https://api.example.com/transactions?page=1',
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Item at index 1 is not an array');

        // Act
        new TransactionListResponse($psrResponse);
    }

    public function test_construct_withEmptyBody_throwsException(): void
    {
        // Arrange
        $psrResponse = $this->createMockResponse('', 200);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response body is empty');

        // Act
        new TransactionListResponse($psrResponse);
    }

    public function test_construct_withInvalidJson_throwsException(): void
    {
        // Arrange
        $psrResponse = $this->createMockResponse('invalid json', 200);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed to decode JSON response');

        // Act
        new TransactionListResponse($psrResponse);
    }

    public function test_fromPsr7Response_createsInstance(): void
    {
        // Arrange
        $responseBody = json_encode([
            'items' => [['id' => 'txn1']],
            'next' => null,
            'first' => 'https://api.example.com/transactions?page=1',
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Act
        $response = TransactionListResponse::fromPsr7Response($psrResponse);

        // Assert
        $this->assertInstanceOf(TransactionListResponse::class, $response);
        $this->assertCount(1, $response->getTransactions());
    }

    public function test_getStatusCode_returnsCorrectCode(): void
    {
        // Arrange
        $responseBody = json_encode([
            'items' => [['id' => 'txn1']],
            'next' => null,
            'first' => 'https://api.example.com/transactions?page=1',
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Act
        $response = new TransactionListResponse($psrResponse);

        // Assert
        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_getPsr7Response_returnsOriginalResponse(): void
    {
        // Arrange
        $responseBody = json_encode([
            'items' => [['id' => 'txn1']],
            'next' => null,
            'first' => 'https://api.example.com/transactions?page=1',
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Act
        $response = new TransactionListResponse($psrResponse);

        // Assert
        $this->assertSame($psrResponse, $response->getPsr7Response());
    }

    public function test_construct_withoutPaginationLinks_setsThemToNull(): void
    {
        // Arrange
        $responseBody = json_encode([
            'items' => [['id' => 'txn1']],
            // No 'next' or 'first' in response
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 200);

        // Act
        $response = new TransactionListResponse($psrResponse);

        // Assert
        $this->assertNull($response->getNextPage());
        $this->assertNull($response->getFirstPage());
        $this->assertFalse($response->hasMorePages());
    }
}
