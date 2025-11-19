<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\Batch;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\Batch\RetrieveBatchRequest;
use PHPUnit\Framework\TestCase;

class RetrieveBatchRequestTest extends TestCase
{
    public function test_construct_withValidId_createsInstance(): void
    {
        $request = new RetrieveBatchRequest('batch123');

        $this->assertSame('batch123', $request->getBatchId());
    }

    public function test_construct_withEmptyId_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Batch ID cannot be empty');

        new RetrieveBatchRequest('');
    }

    public function test_build_createsValidPsr7Request(): void
    {
        $request = new RetrieveBatchRequest('batch456');

        $psr7Request = $request->build();

        $this->assertSame('GET', $psr7Request->getMethod());
        $this->assertStringContainsString('/batches/batch456', (string) $psr7Request->getUri());
        $this->assertSame('application/json', $psr7Request->getHeaderLine('Accept'));
    }

    public function test_build_withCustomBaseUri_usesCustomUri(): void
    {
        $customUri = 'https://custom.api.example.com';
        $request = new RetrieveBatchRequest('batch789', baseUri: $customUri);

        $psr7Request = $request->build();

        $this->assertStringStartsWith($customUri, (string) $psr7Request->getUri());
        $this->assertStringContainsString('/batches/batch789', (string) $psr7Request->getUri());
    }

    public function test_build_withLongBatchId_createsRequest(): void
    {
        $longId = 'wrKK4HcHCXcK3KkXwFRMXVjQ';
        $request = new RetrieveBatchRequest($longId);

        $psr7Request = $request->build();

        $this->assertStringContainsString('/batches/' . $longId, (string) $psr7Request->getUri());
    }

    public function test_getBatchId_returnsCorrectId(): void
    {
        $batchId = 'test-batch-id';
        $request = new RetrieveBatchRequest($batchId);

        $this->assertSame($batchId, $request->getBatchId());
    }
}
