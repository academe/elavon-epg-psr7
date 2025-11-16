<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\ErrorDetail;
use Academe\Elavon\Epg\Psr7\Dtos\ErrorResponse;
use PHPUnit\Framework\TestCase;

/**
 * Tests for ErrorResponse DTO.
 */
class ErrorResponseTest extends TestCase
{
    public function test_construct_withFailures_createsInstance(): void
    {
        // Arrange
        $failure1 = new ErrorDetail('unauthorized', 'API key is invalid');
        $failure2 = new ErrorDetail('validation_error', 'Card number is required', 'card.number');

        // Act
        $errorResponse = new ErrorResponse(
            status: 401,
            failures: [$failure1, $failure2]
        );

        // Assert
        $this->assertSame(401, $errorResponse->status);
        $this->assertCount(2, $errorResponse->failures);
        $this->assertSame($failure1, $errorResponse->failures[0]);
        $this->assertSame($failure2, $errorResponse->failures[1]);
    }

    public function test_construct_withoutFailures_createsInstance(): void
    {
        // Act
        $errorResponse = new ErrorResponse(status: 500);

        // Assert
        $this->assertSame(500, $errorResponse->status);
        $this->assertEmpty($errorResponse->failures);
    }

    public function test_fromArray_withRealApiError_createsInstance(): void
    {
        // Arrange - Real API error from Elavon
        $data = [
            'status' => 401,
            'failures' => [
                [
                    'code' => 'unauthorized',
                    'description' => 'A valid API key is required',
                    'field' => null,
                ],
            ],
        ];

        // Act
        $errorResponse = ErrorResponse::fromArray($data);

        // Assert
        $this->assertSame(401, $errorResponse->status);
        $this->assertCount(1, $errorResponse->failures);
        $this->assertSame('unauthorized', $errorResponse->failures[0]->code);
        $this->assertSame('A valid API key is required', $errorResponse->failures[0]->description);
        $this->assertNull($errorResponse->failures[0]->field);
    }

    public function test_fromArray_withMultipleFailures_createsInstance(): void
    {
        // Arrange
        $data = [
            'status' => 400,
            'failures' => [
                [
                    'code' => 'validation_error',
                    'description' => 'Card number is required',
                    'field' => 'card.number',
                ],
                [
                    'code' => 'validation_error',
                    'description' => 'CVV is required',
                    'field' => 'card.securityCode',
                ],
            ],
        ];

        // Act
        $errorResponse = ErrorResponse::fromArray($data);

        // Assert
        $this->assertSame(400, $errorResponse->status);
        $this->assertCount(2, $errorResponse->failures);
    }

    public function test_fromArray_withoutFailures_createsInstance(): void
    {
        // Arrange
        $data = ['status' => 500];

        // Act
        $errorResponse = ErrorResponse::fromArray($data);

        // Assert
        $this->assertSame(500, $errorResponse->status);
        $this->assertEmpty($errorResponse->failures);
    }

    public function test_toArray_returnsCorrectStructure(): void
    {
        // Arrange
        $data = [
            'status' => 401,
            'failures' => [
                [
                    'code' => 'unauthorized',
                    'description' => 'A valid API key is required',
                ],
            ],
        ];
        $errorResponse = ErrorResponse::fromArray($data);

        // Act
        $array = $errorResponse->toArray();

        // Assert
        $this->assertSame(401, $array['status']);
        $this->assertIsArray($array['failures']);
        $this->assertCount(1, $array['failures']);
        $this->assertSame('unauthorized', $array['failures'][0]['code']);
    }

    public function test_getMessage_withFailures_returnsPrimaryMessage(): void
    {
        // Arrange
        $data = [
            'status' => 401,
            'failures' => [
                ['code' => 'unauthorized', 'description' => 'Invalid API key'],
                ['code' => 'other', 'description' => 'Other error'],
            ],
        ];
        $errorResponse = ErrorResponse::fromArray($data);

        // Act
        $message = $errorResponse->getMessage();

        // Assert
        $this->assertSame('Invalid API key', $message);
    }

    public function test_getMessage_withoutFailures_returnsGenericMessage(): void
    {
        // Arrange
        $errorResponse = new ErrorResponse(status: 500);

        // Act
        $message = $errorResponse->getMessage();

        // Assert
        $this->assertSame('HTTP 500 error', $message);
    }

    public function test_getCode_withFailures_returnsPrimaryCode(): void
    {
        // Arrange
        $data = [
            'status' => 401,
            'failures' => [
                ['code' => 'unauthorized', 'description' => 'Invalid API key'],
            ],
        ];
        $errorResponse = ErrorResponse::fromArray($data);

        // Act
        $code = $errorResponse->getCode();

        // Assert
        $this->assertSame('unauthorized', $code);
    }

    public function test_getCode_withoutFailures_returnsUnknown(): void
    {
        // Arrange
        $errorResponse = new ErrorResponse(status: 500);

        // Act
        $code = $errorResponse->getCode();

        // Assert
        $this->assertSame('unknown', $code);
    }

    public function test_hasErrorCode_withMatchingCode_returnsTrue(): void
    {
        // Arrange
        $data = [
            'status' => 401,
            'failures' => [
                ['code' => 'unauthorized', 'description' => 'Invalid API key'],
                ['code' => 'validation_error', 'description' => 'Field error'],
            ],
        ];
        $errorResponse = ErrorResponse::fromArray($data);

        // Act & Assert
        $this->assertTrue($errorResponse->hasErrorCode('unauthorized'));
        $this->assertTrue($errorResponse->hasErrorCode('validation_error'));
    }

    public function test_hasErrorCode_withNonMatchingCode_returnsFalse(): void
    {
        // Arrange
        $data = [
            'status' => 401,
            'failures' => [
                ['code' => 'unauthorized', 'description' => 'Invalid API key'],
            ],
        ];
        $errorResponse = ErrorResponse::fromArray($data);

        // Act & Assert
        $this->assertFalse($errorResponse->hasErrorCode('validation_error'));
    }

    public function test_getFailures_returnsAllFailures(): void
    {
        // Arrange
        $data = [
            'status' => 400,
            'failures' => [
                ['code' => 'validation_error', 'description' => 'Card number is required', 'field' => 'card.number'],
                ['code' => 'validation_error', 'description' => 'CVV is required', 'field' => 'card.securityCode'],
            ],
        ];
        $errorResponse = ErrorResponse::fromArray($data);

        // Act
        $failures = $errorResponse->getFailures();

        // Assert
        $this->assertIsArray($failures);
        $this->assertCount(2, $failures);
        $this->assertSame('validation_error', $failures[0]->code);
        $this->assertSame('card.number', $failures[0]->field);
        $this->assertSame('validation_error', $failures[1]->code);
        $this->assertSame('card.securityCode', $failures[1]->field);
    }
}
