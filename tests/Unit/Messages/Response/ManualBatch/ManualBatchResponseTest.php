<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Response\ManualBatch;

use Academe\Elavon\Epg\Psr7\Dtos\ErrorResponse;
use Academe\Elavon\Epg\Psr7\Dtos\ManualBatch;
use Academe\Elavon\Epg\Psr7\Messages\Response\ManualBatch\ManualBatchResponse;
use Academe\Elavon\Epg\Psr7\Support\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * Tests for ManualBatchResponse.
 */
class ManualBatchResponseTest extends TestCase
{
    private function createMockResponse(string $body, int $statusCode): ResponseInterface
    {
        $stream = new Stream($body);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn($statusCode);

        return $response;
    }

    public function test_fromPsr7Response_withSuccessResponse_parsesManualBatch(): void
    {
        // Arrange
        $responseBody = json_encode([
            'href' => 'https://api.example.com/manual-batches/mb123',
            'id' => 'mb123',
            'createdAt' => '2025-01-01T00:00:00Z',
            'modifiedAt' => '2025-01-02T00:00:00Z',
            'merchant' => 'https://api.example.com/merchants/m123',
            'customReference' => 'batch-2024-01',
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 201);

        // Act
        $response = ManualBatchResponse::fromPsr7Response($psrResponse);

        // Assert
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->hasError());
        $this->assertInstanceOf(ManualBatch::class, $response->getManualBatch());
        $this->assertSame('mb123', $response->getManualBatch()->id);
        $this->assertSame('batch-2024-01', $response->getManualBatch()->customReference);
        $this->assertSame(201, $response->getStatusCode());
    }

    public function test_fromPsr7Response_withErrorResponse_parsesError(): void
    {
        // Arrange
        $responseBody = json_encode([
            'status' => 400,
            'failures' => [
                ['code' => 'invalid_request', 'description' => 'Invalid request'],
            ],
        ]);
        $psrResponse = $this->createMockResponse($responseBody, 400);

        // Act
        $response = ManualBatchResponse::fromPsr7Response($psrResponse);

        // Assert
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->hasError());
        $this->assertNull($response->getManualBatch());
        $this->assertInstanceOf(ErrorResponse::class, $response->getError());
        $this->assertSame(400, $response->getError()->status);
    }
}
