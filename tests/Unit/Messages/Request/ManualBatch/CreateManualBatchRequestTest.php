<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\ManualBatch;

use Academe\Elavon\Epg\Psr7\Dtos\ManualBatch;
use Academe\Elavon\Epg\Psr7\Messages\Request\ManualBatch\CreateManualBatchRequest;
use PHPUnit\Framework\TestCase;

/**
 * Tests for CreateManualBatchRequest.
 */
class CreateManualBatchRequestTest extends TestCase
{
    public function test_construct_withManualBatchObject_createsInstance(): void
    {
        // Arrange
        $manualBatch = new ManualBatch(
            customReference: 'batch-2024-01',
        );

        // Act
        $request = new CreateManualBatchRequest($manualBatch);

        // Assert
        $this->assertInstanceOf(CreateManualBatchRequest::class, $request);
        $this->assertSame($manualBatch, $request->getManualBatch());
    }

    public function test_construct_withArray_createsInstance(): void
    {
        // Arrange
        $data = [
            'customReference' => 'batch-2024-02',
            'customFields' => ['purpose' => 'test'],
        ];

        // Act
        $request = new CreateManualBatchRequest($data);

        // Assert
        $this->assertInstanceOf(CreateManualBatchRequest::class, $request);
        $this->assertSame('batch-2024-02', $request->getManualBatch()->customReference);
    }

    public function test_build_returnsValidPsr7Request(): void
    {
        // Arrange
        $manualBatch = new ManualBatch(
            customReference: 'test-batch',
        );
        $createRequest = new CreateManualBatchRequest($manualBatch);

        // Act
        $psrRequest = $createRequest->build();

        // Assert
        $this->assertSame('POST', $psrRequest->getMethod());
        $this->assertStringContainsString('/manual-batches', (string) $psrRequest->getUri());
        $this->assertSame('application/json', $psrRequest->getHeaderLine('Content-Type'));
        $this->assertSame('application/json', $psrRequest->getHeaderLine('Accept'));

        // Verify body contains the data
        $body = (string) $psrRequest->getBody();
        $decodedBody = json_decode($body, true);
        $this->assertSame('test-batch', $decodedBody['customReference']);
    }
}
