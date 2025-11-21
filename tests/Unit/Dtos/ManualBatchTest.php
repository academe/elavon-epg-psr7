<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\ManualBatch;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for ManualBatch DTO.
 */
class ManualBatchTest extends TestCase
{
    public function test_construct_withMinimalData_createsInstance(): void
    {
        // Act
        $manualBatch = new ManualBatch();

        // Assert
        $this->assertNull($manualBatch->href);
        $this->assertNull($manualBatch->id);
        $this->assertNull($manualBatch->customReference);
    }

    public function test_construct_withAllFields_createsInstance(): void
    {
        // Act
        $manualBatch = new ManualBatch(
            href: 'https://api.example.com/manual-batches/mb123',
            id: 'mb123',
            createdAt: '2025-01-01T00:00:00Z',
            modifiedAt: '2025-01-02T00:00:00Z',
            merchant: 'https://api.example.com/merchants/m123',
            customReference: 'batch-2024-01',
            customFields: ['purpose' => 'daily-settlement'],
        );

        // Assert
        $this->assertSame('https://api.example.com/manual-batches/mb123', $manualBatch->href);
        $this->assertSame('mb123', $manualBatch->id);
        $this->assertSame('2025-01-01T00:00:00Z', $manualBatch->createdAt);
        $this->assertSame('2025-01-02T00:00:00Z', $manualBatch->modifiedAt);
        $this->assertSame('https://api.example.com/merchants/m123', $manualBatch->merchant);
        $this->assertSame('batch-2024-01', $manualBatch->customReference);
        $this->assertSame(['purpose' => 'daily-settlement'], $manualBatch->customFields);
    }

    public function test_construct_withCustomReferenceTooLong_throwsException(): void
    {
        // Arrange
        $tooLongReference = str_repeat('a', 256);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Custom reference must not exceed 255 characters');

        // Act
        new ManualBatch(customReference: $tooLongReference);
    }

    public function test_construct_withCustomFieldNameTooLong_throwsException(): void
    {
        // Arrange
        $tooLongKey = str_repeat('a', 65);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Custom field name must not exceed 64 characters');

        // Act
        new ManualBatch(customFields: [$tooLongKey => 'value']);
    }

    public function test_construct_withCustomFieldValueTooLong_throwsException(): void
    {
        // Arrange
        $tooLongValue = str_repeat('a', 1025);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Custom field value must not exceed 1024 characters');

        // Act
        new ManualBatch(customFields: ['key' => $tooLongValue]);
    }

    public function test_fromData_withValidData_createsInstance(): void
    {
        // Arrange
        $data = [
            'href' => 'https://api.example.com/manual-batches/mb456',
            'id' => 'mb456',
            'createdAt' => '2025-01-15T12:00:00Z',
            'modifiedAt' => '2025-01-16T12:00:00Z',
            'merchant' => 'https://api.example.com/merchants/m456',
            'customReference' => 'ref-789',
            'customFields' => ['status' => 'closed'],
        ];

        // Act
        $manualBatch = ManualBatch::fromData($data);

        // Assert
        $this->assertInstanceOf(ManualBatch::class, $manualBatch);
        $this->assertSame('https://api.example.com/manual-batches/mb456', $manualBatch->href);
        $this->assertSame('mb456', $manualBatch->id);
        $this->assertSame('2025-01-15T12:00:00Z', $manualBatch->createdAt);
        $this->assertSame('2025-01-16T12:00:00Z', $manualBatch->modifiedAt);
        $this->assertSame('https://api.example.com/merchants/m456', $manualBatch->merchant);
        $this->assertSame('ref-789', $manualBatch->customReference);
        $this->assertSame(['status' => 'closed'], $manualBatch->customFields);
    }

    public function test_toData_withAllFields_serializesCorrectly(): void
    {
        // Arrange
        $manualBatch = new ManualBatch(
            href: 'https://api.example.com/manual-batches/mb789',
            id: 'mb789',
            customReference: 'test-ref',
            customFields: ['key' => 'value'],
        );

        // Act
        $data = $manualBatch->toData();

        // Assert
        $this->assertIsArray($data);
        $this->assertSame('https://api.example.com/manual-batches/mb789', $data['href']);
        $this->assertSame('mb789', $data['id']);
        $this->assertSame('test-ref', $data['customReference']);
        $this->assertSame(['key' => 'value'], $data['customFields']);
    }

    public function test_toData_withNullFields_omitsNullValues(): void
    {
        // Arrange
        $manualBatch = new ManualBatch(
            customReference: 'only-ref',
        );

        // Act
        $data = $manualBatch->toData();

        // Assert
        $this->assertIsArray($data);
        $this->assertArrayHasKey('customReference', $data);
        $this->assertArrayNotHasKey('href', $data);
        $this->assertArrayNotHasKey('id', $data);
    }
}
