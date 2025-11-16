<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\ErrorDetail;
use PHPUnit\Framework\TestCase;

/**
 * Tests for ErrorDetail DTO.
 */
class ErrorDetailTest extends TestCase
{
    public function test_construct_withAllProperties_createsInstance(): void
    {
        // Act
        $errorDetail = new ErrorDetail(
            code: 'validation_error',
            description: 'Card number is invalid',
            field: 'card.number'
        );

        // Assert
        $this->assertSame('validation_error', $errorDetail->code);
        $this->assertSame('Card number is invalid', $errorDetail->description);
        $this->assertSame('card.number', $errorDetail->field);
    }

    public function test_construct_withoutField_createsInstance(): void
    {
        // Act
        $errorDetail = new ErrorDetail(
            code: 'unauthorized',
            description: 'A valid API key is required'
        );

        // Assert
        $this->assertSame('unauthorized', $errorDetail->code);
        $this->assertSame('A valid API key is required', $errorDetail->description);
        $this->assertNull($errorDetail->field);
    }

    public function test_fromArray_withAllFields_createsInstance(): void
    {
        // Arrange
        $data = [
            'code' => 'validation_error',
            'description' => 'Card number is invalid',
            'field' => 'card.number',
        ];

        // Act
        $errorDetail = ErrorDetail::fromArray($data);

        // Assert
        $this->assertSame('validation_error', $errorDetail->code);
        $this->assertSame('Card number is invalid', $errorDetail->description);
        $this->assertSame('card.number', $errorDetail->field);
    }

    public function test_fromArray_withNullField_createsInstance(): void
    {
        // Arrange
        $data = [
            'code' => 'unauthorized',
            'description' => 'A valid API key is required',
            'field' => null,
        ];

        // Act
        $errorDetail = ErrorDetail::fromArray($data);

        // Assert
        $this->assertSame('unauthorized', $errorDetail->code);
        $this->assertSame('A valid API key is required', $errorDetail->description);
        $this->assertNull($errorDetail->field);
    }

    public function test_fromArray_withMissingField_createsInstance(): void
    {
        // Arrange
        $data = [
            'code' => 'unauthorized',
            'description' => 'A valid API key is required',
        ];

        // Act
        $errorDetail = ErrorDetail::fromArray($data);

        // Assert
        $this->assertNull($errorDetail->field);
    }

    public function test_toArray_withAllFields_returnsArray(): void
    {
        // Arrange
        $errorDetail = new ErrorDetail(
            code: 'validation_error',
            description: 'Card number is invalid',
            field: 'card.number'
        );

        // Act
        $array = $errorDetail->toArray();

        // Assert
        $this->assertSame([
            'code' => 'validation_error',
            'description' => 'Card number is invalid',
            'field' => 'card.number',
        ], $array);
    }

    public function test_toArray_withNullField_omitsField(): void
    {
        // Arrange
        $errorDetail = new ErrorDetail(
            code: 'unauthorized',
            description: 'A valid API key is required'
        );

        // Act
        $array = $errorDetail->toArray();

        // Assert
        $this->assertSame([
            'code' => 'unauthorized',
            'description' => 'A valid API key is required',
        ], $array);
        $this->assertArrayNotHasKey('field', $array);
    }
}
