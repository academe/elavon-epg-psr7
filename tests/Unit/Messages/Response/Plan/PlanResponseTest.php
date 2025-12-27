<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Response\Plan;

use Academe\Elavon\Epg\Psr7\Dtos\Plan;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Plan\PlanResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Tests for PlanResponse.
 */
class PlanResponseTest extends TestCase
{
    public function test_construct_withSuccessResponse_parsesPlan(): void
    {
        // Arrange
        $responseData = [
            'id' => 'plan123',
            'href' => 'https://api.example.com/plans/plan123',
            'name' => 'Monthly License',
            'billingInterval' => ['timeUnit' => 'month', 'count' => 1],
            'total' => ['amount' => '29.99', 'currencyCode' => 'USD'],
        ];
        $response = $this->createMockResponse(201, $responseData);

        // Act
        $planResponse = PlanResponse::fromPsr7Response($response);

        // Assert
        $this->assertTrue($planResponse->isSuccessful());
        $this->assertNull($planResponse->error);
        $this->assertInstanceOf(Plan::class, $planResponse->plan);
        $this->assertSame('plan123', $planResponse->plan->id);
        $this->assertSame('Monthly License', $planResponse->plan->name);
        $this->assertSame(201, $planResponse->statusCode);
    }

    public function test_construct_withErrorResponse_parsesError(): void
    {
        // Arrange
        $errorData = [
            'failures' => [
                ['code' => 'VALIDATION_ERROR', 'field' => 'name', 'description' => 'Name is required'],
            ],
        ];
        $response = $this->createMockResponse(400, $errorData);

        // Act
        $planResponse = PlanResponse::fromPsr7Response($response);

        // Assert
        $this->assertFalse($planResponse->isSuccessful());
        $this->assertNull($planResponse->plan);
        $this->assertNotNull($planResponse->error);
        $this->assertSame(400, $planResponse->statusCode);
    }

    public function test_construct_withEmptyBody_throwsException(): void
    {
        // Arrange
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $stream->method('__toString')->willReturn('');
        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn(200);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response body is empty');

        // Act
        PlanResponse::fromPsr7Response($response);
    }

    public function test_construct_withInvalidJson_throwsException(): void
    {
        // Arrange
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $stream->method('__toString')->willReturn('invalid json');
        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn(200);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed to decode JSON response');

        // Act
        PlanResponse::fromPsr7Response($response);
    }

    public function test_fromPsr7Response_createsInstance(): void
    {
        // Arrange
        $responseData = [
            'id' => 'plan456',
            'name' => 'Annual Plan',
            'billingInterval' => ['timeUnit' => 'year', 'count' => 1],
            'total' => ['amount' => '299.99', 'currencyCode' => 'EUR'],
        ];
        $response = $this->createMockResponse(200, $responseData);

        // Act
        $planResponse = PlanResponse::fromPsr7Response($response);

        // Assert
        $this->assertInstanceOf(PlanResponse::class, $planResponse);
        $this->assertSame('plan456', $planResponse->plan->id);
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
