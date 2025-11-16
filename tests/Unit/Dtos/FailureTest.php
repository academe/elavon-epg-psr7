<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\Failure;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Failure data object.
 */
class FailureTest extends TestCase
{
    public function test_construct_withAllProperties_createsInstance(): void
    {
        // Arrange & Act
        $failure = new Failure(
            code: 'unauthorized',
            description: 'A valid API key is required',
            field: 'apiKey'
        );

        // Assert
        $this->assertSame('unauthorized', $failure->code);
        $this->assertSame('A valid API key is required', $failure->description);
        $this->assertSame('apiKey', $failure->field);
    }

    public function test_construct_withNoProperties_createsInstance(): void
    {
        // Arrange & Act
        $failure = new Failure();

        // Assert
        $this->assertNull($failure->code);
        $this->assertNull($failure->description);
        $this->assertNull($failure->field);
    }

    public function test_fromArray_withAllProperties_createsInstance(): void
    {
        // Arrange
        $data = [
            'code' => 'invalid_card_number',
            'description' => 'The card number format is invalid',
            'field' => 'card.number',
        ];

        // Act
        $failure = Failure::fromArray($data);

        // Assert
        $this->assertSame('invalid_card_number', $failure->code);
        $this->assertSame('The card number format is invalid', $failure->description);
        $this->assertSame('card.number', $failure->field);
    }

    public function test_fromArray_withMissingProperties_createsInstanceWithNulls(): void
    {
        // Arrange
        $data = [
            'code' => 'general_error',
        ];

        // Act
        $failure = Failure::fromArray($data);

        // Assert
        $this->assertSame('general_error', $failure->code);
        $this->assertNull($failure->description);
        $this->assertNull($failure->field);
    }

    public function test_fromArray_withEmptyArray_createsInstanceWithNulls(): void
    {
        // Arrange
        $data = [];

        // Act
        $failure = Failure::fromArray($data);

        // Assert
        $this->assertNull($failure->code);
        $this->assertNull($failure->description);
        $this->assertNull($failure->field);
    }

    public function test_toArray_withAllProperties_returnsCompleteArray(): void
    {
        // Arrange
        $failure = new Failure(
            code: 'declined',
            description: 'The transaction was declined',
            field: 'transaction'
        );

        // Act
        $result = $failure->toArray();

        // Assert
        $this->assertSame([
            'code' => 'declined',
            'description' => 'The transaction was declined',
            'field' => 'transaction',
        ], $result);
    }

    public function test_toArray_withNullProperties_excludesNullValues(): void
    {
        // Arrange
        $failure = new Failure(
            code: 'error',
            description: null,
            field: null
        );

        // Act
        $result = $failure->toArray();

        // Assert
        $this->assertSame(['code' => 'error'], $result);
        $this->assertArrayNotHasKey('description', $result);
        $this->assertArrayNotHasKey('field', $result);
    }

    public function test_toArray_roundTrip_preservesData(): void
    {
        // Arrange
        $originalData = [
            'code' => 'validation_error',
            'description' => 'Validation failed',
            'field' => 'email',
        ];
        $failure = Failure::fromArray($originalData);

        // Act
        $result = $failure->toArray();

        // Assert
        $this->assertSame($originalData, $result);
    }
}
